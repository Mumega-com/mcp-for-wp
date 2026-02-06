/**
 * Site Pilot AI Admin JavaScript
 *
 * @package SitePilotAI
 */

(function($) {
	'use strict';

	/**
	 * Copy to clipboard functionality
	 */
	function initCopyButtons() {
		// API key copy buttons
		$('.spai-copy-btn').on('click', function() {
			var btn = $(this);
			var text = btn.data('copy') || $('#spai-api-key').val();

			copyToClipboard(text).then(function() {
				showCopyFeedback(btn);
			}).catch(function() {
				alert(spaiAdmin.strings.copyFailed);
			});
		});

		// Code block copy buttons
		$('.spai-copy-code-btn').on('click', function() {
			var btn = $(this);
			var targetId = btn.data('target');
			var text = $('#' + targetId).text();

			copyToClipboard(text).then(function() {
				showCopyFeedback(btn);
			}).catch(function() {
				alert(spaiAdmin.strings.copyFailed);
			});
		});
	}

	/**
	 * Show copy feedback on button
	 */
	function showCopyFeedback(btn) {
		var originalHtml = btn.html();
		btn.html('<span class="dashicons dashicons-yes"></span> ' + spaiAdmin.strings.copied);
		btn.prop('disabled', true);
		setTimeout(function() {
			btn.html(originalHtml);
			btn.prop('disabled', false);
		}, 2000);
	}

	/**
	 * Copy text to clipboard
	 */
	function copyToClipboard(text) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText(text);
		}

		return new Promise(function(resolve, reject) {
			var textarea = document.createElement('textarea');
			textarea.value = text;
			textarea.style.position = 'fixed';
			textarea.style.opacity = '0';
			document.body.appendChild(textarea);
			textarea.select();

			try {
				var successful = document.execCommand('copy');
				document.body.removeChild(textarea);
				if (successful) {
					resolve();
				} else {
					reject();
				}
			} catch (err) {
				document.body.removeChild(textarea);
				reject(err);
			}
		});
	}

	/**
	 * Confirm regenerate key
	 */
	function initRegenerateConfirm() {
		$('.spai-regenerate-btn').on('click', function(e) {
			if (!confirm(spaiAdmin.strings.confirm)) {
				e.preventDefault();
			}
		});
	}

	/**
	 * Test connection
	 */
	function initTestConnection() {
		$('#spai-test-btn').on('click', function() {
			var btn = $(this);
			var result = $('#spai-test-result');
			var success = result.find('.spai-test-success');
			var error = result.find('.spai-test-error');
			var details = result.find('.spai-test-details');

			// Show loading state
			btn.prop('disabled', true);
			btn.html('<span class="dashicons dashicons-update spai-spin"></span> ' + spaiAdmin.strings.testing);
			result.show();
			success.hide();
			error.hide();
			details.hide();

			$.ajax({
				url: spaiAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'spai_test_connection',
					nonce: spaiAdmin.nonce
				},
				success: function(response) {
					btn.prop('disabled', false);
					btn.html('<span class="dashicons dashicons-yes-alt"></span> Test Connection');

					if (response.success) {
						success.show().find('.spai-test-message').text(
							spaiAdmin.strings.connected + ' ' + response.data.site_name
						);
						details.show().html(
							'<table>' +
							'<tr><td>Site:</td><td>' + escHtml(response.data.site_name) + '</td></tr>' +
							'<tr><td>WordPress:</td><td>' + escHtml(response.data.wp_version) + '</td></tr>' +
							'<tr><td>PHP:</td><td>' + escHtml(response.data.php_version) + '</td></tr>' +
							'<tr><td>Plugin:</td><td>v' + escHtml(response.data.plugin_version) + '</td></tr>' +
							'<tr><td>REST URL:</td><td>' + escHtml(response.data.rest_url) + '</td></tr>' +
							'</table>'
						);
						result.css('background', '#d4edda');
					} else {
						error.show().find('.spai-test-message').text(
							spaiAdmin.strings.testFailed + ': ' + (response.data ? response.data.message : 'Unknown error')
						);
						result.css('background', '#f8d7da');
					}
				},
				error: function() {
					btn.prop('disabled', false);
					btn.html('<span class="dashicons dashicons-yes-alt"></span> Test Connection');
					error.show().find('.spai-test-message').text(spaiAdmin.strings.testFailed);
					result.css('background', '#f8d7da');
				}
			});
		});
	}

	/**
	 * Dismiss welcome banner
	 */
	function initDismissWelcome() {
		$('#spai-dismiss-welcome').on('click', function() {
			var btn = $(this);
			btn.prop('disabled', true);

			$.ajax({
				url: spaiAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'spai_dismiss_welcome',
					nonce: spaiAdmin.nonce
				},
				success: function() {
					$('#spai-welcome').slideUp(300);
				},
				error: function() {
					$('#spai-welcome').slideUp(300);
				}
			});
		});
	}

	/**
	 * Escape HTML for safe insertion
	 */
	function escHtml(str) {
		if (!str) return '';
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}

	/**
	 * Initialize
	 */
	$(document).ready(function() {
		initCopyButtons();
		initRegenerateConfirm();
		initTestConnection();
		initDismissWelcome();
	});

})(jQuery);
