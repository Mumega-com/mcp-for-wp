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
		$('.spai-copy-btn').on('click', function() {
			var btn = $(this);
			var text = btn.data('copy') || $('#spai-api-key').val();

			copyToClipboard(text).then(function() {
				var originalText = btn.text();
				btn.text(spaiAdmin.strings.copied);
				setTimeout(function() {
					btn.text(originalText);
				}, 2000);
			}).catch(function() {
				alert(spaiAdmin.strings.copyFailed);
			});
		});
	}

	/**
	 * Copy text to clipboard
	 * @param {string} text Text to copy
	 * @return {Promise}
	 */
	function copyToClipboard(text) {
		// Try modern API first
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText(text);
		}

		// Fallback for older browsers
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
	 * Initialize
	 */
	$(document).ready(function() {
		initCopyButtons();
		initRegenerateConfirm();
	});

})(jQuery);
