document.addEventListener('DOMContentLoaded', () => {
	window.PhotonicWPNativeUI = function(fnSendDataToWizard, fnGetDataFromWizard) {
		let messageChannel, mediaLibrary;
		const wpEditor = document.getElementById('wp-content-wrap'),
			mceTab = document.getElementById('content-tmce'),
			htmlTab = document.getElementById('content-html');
		let lastClickedTab = wpEditor ? (wpEditor.classList.contains('tmce-active') ? mceTab : htmlTab) : htmlTab;

		const initializeMediaLibrary = function(mediaOptions, photonicOptions) {
			mediaLibrary = top.wp.media(mediaOptions);

			mediaLibrary.on('select', () => {
				let selection = mediaLibrary.state().get('selection');
				let selected_data = '';
				selection.map(function (attachment) {
					attachment = attachment.toJSON();
					selected_data += attachment.id + ',';
				});
				selected_data = selected_data.replace(/^,+|,+$/g, '');

				messageChannel.port1.postMessage({
					type: 'photonicReceiveMediaLibrarySelections',
					selection: selected_data,
					options: photonicOptions
				});
			});

			mediaLibrary.on('open', () => {
				const selection = mediaLibrary.state().get('selection');
				let ids = photonicOptions.selectedIds;
				const shortcodeTag = photonicOptions.shortcodeTag;
				const isBlock = photonicOptions.isBlock;

				let editor_selection = photonicOptions.currentShortcode;
				let shortcode, attrs, win = window.dialogArguments || opener || parent || top;
				if (!isBlock && editor_selection && editor_selection !== '') {
					shortcode = top.wp.shortcode.next(shortcodeTag, editor_selection);
					attrs = shortcode.shortcode.attrs.named;
				}
				else if (isBlock) { // Gutenberg
					shortcode = photonicOptions.currentShortcode;
					if (shortcode && shortcode !== '') {
						attrs = JSON.parse(shortcode);
					}
				}

				if (ids === '' && attrs !== undefined) {
					if (attrs.ids !== undefined) {
						ids = attrs.ids;
					}
					else if (attrs.include !== undefined) {
						ids = attrs.include;
					}
				}


				ids = ids.split(',');
				ids.forEach(function (id) {
					const attachment = top.wp.media.attachment(id);
					attachment.fetch();
					selection.add(attachment ? [attachment] : []);
				});
			});
		}

		const open = function() {
			if (mediaLibrary) {
				mediaLibrary.open();
			}
		}

		const closeTB = function() {
			if (typeof tb_close === 'function') {
				tb_close();
			}
			else {
				if (document.getElementById('TB_window')) {
					document.getElementById('TB_window').remove();
				}
				if (document.getElementById('TB_overlay')) {
					document.getElementById('TB_overlay').remove();
				}
			}
		}

		const waitForIFrame = function() {
			const observer = new MutationObserver(() => {
				let iframe;
				iframe = document.querySelector('#TB_iframeContent');
				if (iframe) {
					iframe.onload = () => {
						messageChannel = new MessageChannel();
						// nativeMediaLibrary = new PhotonicWPNativeUI(sourceMessageChannel);

						messageChannel.port1.onmessage = (event) => {
							if (event.data.type === 'photonicAddTBClass') {
								document.getElementById('TB_window').classList.add('photonic-tb');
							}
							else if (event.data.type === 'photonicInitializeMediaLibrary') {
								initializeMediaLibrary(event.data.mediaOptions, event.data.photonicOptions);
							}
							else if (event.data.type === 'photonicOpenMediaLibrary') {
								open();
							}
							else if (event.data.type === 'photonicUpdateGallery') {
								fnGetDataFromWizard(event.data);
								closeTB();
							}
						};

						// First, send a placeholder message to the iFrame and transfer port2 to it
						iframe.contentWindow.postMessage('init', '/', [messageChannel.port2]);

						// Second, send the actual shortcode
						fnSendDataToWizard(messageChannel);
					};
				}
			});

			observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		};

		return {
			waitForIFrame: waitForIFrame
		};
	}
});
