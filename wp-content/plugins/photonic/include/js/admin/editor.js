document.addEventListener('DOMContentLoaded', () => {
	const PhotonicEditor = () => {
		const wpEditor = document.getElementById('wp-content-wrap');
		const wizardButton = document.querySelector('#photonic-add-gallery');
		const mceTab = document.getElementById('content-tmce'), htmlTab = document.getElementById('content-html');
		const nativeMediaLibrary = new PhotonicWPNativeUI(sendDataToWizard, getDataFromWizard);
		let photonicEditorSelection = '';

		const isBlock = !!(wp.data && wp.data.select('core/block-editor') && document.body.classList.contains('block-editor-page'));
		let isTinyMCE = wpEditor ? wpEditor.classList.contains('tmce-active') : false;
		let lastClickedTab = isTinyMCE ? mceTab : htmlTab;

		if (!isBlock && !isTinyMCE) {
			nativeMediaLibrary.waitForIFrame();
		}

		if (wizardButton) {
			wizardButton.addEventListener('click', e => {
				const textArea = document.querySelector('textarea#content');
				const start = textArea.selectionStart;
				const end = textArea.selectionEnd;
				photonicEditorSelection = textArea.value.substring(start, end);
			});
		}

		if (mceTab && htmlTab) {
			[mceTab, htmlTab].forEach(button => {
				button.addEventListener('click', e => {
					if (lastClickedTab.id !== button.id) {
						lastClickedTab = button;
						if (button.id === htmlTab.id) {
							nativeMediaLibrary.waitForIFrame();
						}
					}
				});
			});
		}

		function parseEditorSelection(selection) {
			if (selection !== '' && top.wp !== undefined && top.wp.shortcode !== undefined) {
				let shortcode = top.wp.shortcode.next(Photonic_Editor_JS.shortcode, selection.trim());
				let moreShortcode = top.wp.shortcode.next(Photonic_Editor_JS.shortcode, selection.trim(), 1); // Only one shortcode at a time

				if (shortcode !== undefined && moreShortcode === undefined && shortcode.content.length === selection.trim().length) { // Selection is a valid shortcode
					return {
						shortcode: shortcode
					}
				}
				else { // Selection is not a valid shortcode
					return {
						shortcode: null,
						error: 'Not a shortcode'
					}
				}
			}
			else if (selection === '') { // Selection is blank
				return {
					shortcode: ''
				}
			}
			return null;
		}

		function sendDataToWizard(messageChannel) {
			messageChannel.port1.postMessage({
				type: 'photonicShortcode',
				object: parseEditorSelection(photonicEditorSelection),
			});
		}

		function getDataFromWizard(data) {
			const win = window.dialogArguments || opener || parent || top;
			win.send_to_editor(data.html);
		}
	};

	PhotonicEditor();
});
