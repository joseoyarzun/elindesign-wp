/**
 * widget.js - Contains all Widget functionality required by Photonic
 */
document.addEventListener('DOMContentLoaded', () => {
	const PhotonicWidget = () => {
		"use strict";

		let nativeMediaLibrary = new PhotonicWPNativeUI(sendDataToWizard, getDataFromWizard);
		let photonicWidgetData;
		let shortcode = '';
		let clickedNode;

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('.photonic-wizard')) {
				return;
			}
			e.preventDefault();

			clickedNode = e.target.closest('.photonic-wizard');
			photonicWidgetData = clickedNode.closest('.photonic-widget').querySelector('.photonic-shortcode');
			shortcode = photonicWidgetData.value;

			tb_show('Click to create gallery', clickedNode.getAttribute('href'));
		});

		nativeMediaLibrary.waitForIFrame();

		function processWidgetChanges(widgetShortcode) {
			const widget = clickedNode.closest('.photonic-widget');
			widget.querySelector('.photonic-shortcode').value = widgetShortcode;
			if (top.wp !== undefined && top.wp.shortcode !== undefined) {
				const shortcode = top.wp.shortcode.next(Photonic_Widget_JS.shortcode, widgetShortcode);
				const attrs = shortcode.shortcode.attrs.named;
				if (clickedNode.classList.contains('photonic')) {
					clickedNode.parentElement.querySelector('p').innerHTML = Photonic_Widget_JS.edit_message;
				}
				if (attrs.type !== undefined) {
					clickedNode.className = '';
					clickedNode.classList.add('photonic-wizard', attrs.type);
				}
				else {
					clickedNode.className = '';
					clickedNode.classList.add('photonic-wizard', 'wp');
				}
			}

			widget.querySelector('.photonic-shortcode-display').innerHTML = "<h4>" + Photonic_Widget_JS.current_shortcode + "</h4>\n" +
				"<code>" + widgetShortcode + "</code>\n";

			widget.querySelector('input[type="text"]').dispatchEvent(new Event('change', {bubbles: true}));
		}

		function parseSelection(selection) {
			if (selection !== '' && top.wp !== undefined && top.wp.shortcode !== undefined) {
				let shortcode = top.wp.shortcode.next(Photonic_Widget_JS.shortcode, selection.trim());
				let moreShortcode = top.wp.shortcode.next(Photonic_Widget_JS.shortcode, selection.trim(), 1); // Only one shortcode at a time

				if (shortcode !== undefined && moreShortcode === undefined && shortcode.content.length === selection.trim().length) { // Selection is a valid shortcode
					return {
						shortcode: shortcode
					}
				} else { // Selection is not a valid shortcode
					return {
						shortcode: null,
						error: 'Not a shortcode'
					}
				}
			} else if (selection === '') { // Selection is blank
				return {
					shortcode: ''
				}
			}
			return null;
		}

		function sendDataToWizard(messageChannel) {
			messageChannel.port1.postMessage({
				type: 'photonicWidget',
				object: parseSelection(shortcode),
			});
		}

		function getDataFromWizard(data) {
			processWidgetChanges(data.html);
		}
	};

	PhotonicWidget();
});
