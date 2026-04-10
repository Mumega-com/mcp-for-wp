<?php
/**
 * Chat tab — AI assistant built into WP Admin.
 *
 * @package SitePilotAI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$chat_endpoint = get_option( 'spai_chat_endpoint', 'https://mumcp-chat.mumega.workers.dev' );
$site_name     = get_bloginfo( 'name' );
$site_url      = home_url();
$plugin_ver    = SPAI_VERSION;
?>
<div class="wrap">
	<h1>
		<span class="dashicons dashicons-format-chat"></span>
		<?php esc_html_e( 'mumcp Chat', 'site-pilot-ai' ); ?>
	</h1>
	<p class="description"><?php esc_html_e( 'Talk to your site. Ask it to build pages, edit content, manage products — all through natural language.', 'site-pilot-ai' ); ?></p>

	<div id="spai-chat-container" style="max-width:800px; margin-top:20px;">
		<div id="spai-chat-messages" style="
			border: 1px solid #c3c4c7;
			border-radius: 8px;
			background: #fff;
			height: 500px;
			overflow-y: auto;
			padding: 20px;
			margin-bottom: 12px;
		">
			<div class="spai-chat-msg spai-chat-assistant" style="margin-bottom:16px;">
				<strong style="color:#2271b1;">mumcp</strong>
				<p style="margin:4px 0 0;">
					<?php
					printf(
						esc_html__( 'Hi! I can help you manage %s. Try: "Build a services page" or "List all pages" or "Add a testimonials section to the homepage."', 'site-pilot-ai' ),
						esc_html( $site_name )
					);
					?>
				</p>
			</div>
		</div>

		<div style="display:flex; gap:8px;">
			<input type="text" id="spai-chat-input" placeholder="<?php esc_attr_e( 'Type a message...', 'site-pilot-ai' ); ?>" style="
				flex: 1;
				padding: 10px 14px;
				font-size: 14px;
				border: 1px solid #c3c4c7;
				border-radius: 6px;
			" />
			<button type="button" id="spai-chat-send" class="button button-primary" style="padding: 10px 20px;">
				<?php esc_html_e( 'Send', 'site-pilot-ai' ); ?>
			</button>
		</div>

		<p class="description" style="margin-top:8px;">
			<?php esc_html_e( 'Powered by Workers AI. Your data stays on Cloudflare edge + your WordPress site.', 'site-pilot-ai' ); ?>
		</p>
	</div>
</div>

<script>
(function() {
	const messagesEl = document.getElementById('spai-chat-messages');
	const inputEl = document.getElementById('spai-chat-input');
	const sendBtn = document.getElementById('spai-chat-send');
	const ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
	const ajaxNonce = <?php echo wp_json_encode( wp_create_nonce( 'spai_admin_nonce' ) ); ?>;

	let history = [];

	function addMessage(role, text) {
		const div = document.createElement('div');
		div.className = 'spai-chat-msg spai-chat-' + role;
		div.style.marginBottom = '16px';

		const name = document.createElement('strong');
		name.textContent = role === 'user' ? 'You' : 'mumcp';
		name.style.color = role === 'user' ? '#1d2327' : '#2271b1';

		const p = document.createElement('p');
		p.style.margin = '4px 0 0';
		p.style.whiteSpace = 'pre-wrap';
		p.textContent = text;

		div.appendChild(name);
		div.appendChild(p);
		messagesEl.appendChild(div);
		messagesEl.scrollTop = messagesEl.scrollHeight;
	}

	function addToolResult(toolCall, result) {
		const div = document.createElement('div');
		div.className = 'spai-chat-msg spai-chat-tool';
		div.style.marginBottom = '16px';
		div.style.padding = '10px';
		div.style.background = '#f0f6fc';
		div.style.borderRadius = '6px';
		div.style.fontFamily = 'monospace';
		div.style.fontSize = '12px';

		const label = document.createElement('strong');
		label.textContent = '⚡ ' + toolCall.tool;
		label.style.color = '#2271b1';

		const pre = document.createElement('pre');
		pre.style.margin = '8px 0 0';
		pre.style.whiteSpace = 'pre-wrap';
		pre.style.fontSize = '11px';
		pre.textContent = typeof result === 'string' ? result : JSON.stringify(result, null, 2).substring(0, 500);

		div.appendChild(label);
		div.appendChild(pre);
		messagesEl.appendChild(div);
		messagesEl.scrollTop = messagesEl.scrollHeight;
	}

	async function executeTool(toolCall) {
		// Execute via local MCP endpoint
		const mcpUrl = <?php echo wp_json_encode( rest_url( 'site-pilot-ai/v1/mcp' ) ); ?>;
		const mcpKey = <?php
			// Get the first active scoped key, or fall back to legacy key
			$keys = ( new Spai_Admin() )->list_scoped_api_keys( true );
			$active = array_filter( $keys, function( $k ) { return empty( $k['revoked_at'] ); } );
			$first = reset( $active );
			echo wp_json_encode( $first ? $first['plain_key'] ?? '' : '' );
		?>;

		try {
			const resp = await fetch(mcpUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-API-Key': mcpKey || '<?php echo esc_js( get_transient( 'spai_new_api_key' ) ?: '' ); ?>',
				},
				body: JSON.stringify({
					jsonrpc: '2.0',
					id: Date.now(),
					method: 'tools/call',
					params: {
						name: toolCall.tool,
						arguments: toolCall.arguments || {},
					},
				}),
			});

			const data = await resp.json();
			if (data.result && data.result.content) {
				const text = data.result.content[0]?.text || JSON.stringify(data.result);
				try {
					return JSON.parse(text);
				} catch {
					return text;
				}
			}
			return data.error || data;
		} catch (err) {
			return { error: err.message };
		}
	}

	async function sendMessage() {
		const message = inputEl.value.trim();
		if (!message) return;

		inputEl.value = '';
		inputEl.disabled = true;
		sendBtn.disabled = true;

		addMessage('user', message);
		history.push({ role: 'user', content: message });

		try {
			const formData = new FormData();
			formData.append('action', 'spai_chat');
			formData.append('nonce', ajaxNonce);
			formData.append('message', message);
			formData.append('history', JSON.stringify(history.slice(-10)));

			const resp = await fetch(ajaxUrl, { method: 'POST', body: formData });
			const ajaxResp = await resp.json();

			if (!ajaxResp.success) {
				throw new Error(ajaxResp.data?.message || 'Chat failed');
			}
			const data = ajaxResp.data;

			if (data.tool_call) {
				addMessage('assistant', 'Running: ' + data.tool_call.tool + '...');
				const result = await executeTool(data.tool_call);
				addToolResult(data.tool_call, result);

				// Tell the AI what happened
				const summary = typeof result === 'object'
					? (result.success ? 'Success' : 'Error: ' + (result.error || JSON.stringify(result)).substring(0, 200))
					: String(result).substring(0, 200);
				history.push({ role: 'assistant', content: 'Executed ' + data.tool_call.tool + '. Result: ' + summary });
			} else if (data.response) {
				addMessage('assistant', data.response);
				history.push({ role: 'assistant', content: data.response });
			} else if (data.error) {
				addMessage('assistant', '⚠️ ' + data.error);
			}
		} catch (err) {
			addMessage('assistant', '⚠️ Connection error: ' + err.message);
		}

		inputEl.disabled = false;
		sendBtn.disabled = false;
		inputEl.focus();
	}

	sendBtn.addEventListener('click', sendMessage);
	inputEl.addEventListener('keydown', function(e) {
		if (e.key === 'Enter') sendMessage();
	});
})();
</script>
