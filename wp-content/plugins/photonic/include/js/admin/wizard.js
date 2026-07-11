document.addEventListener('DOMContentLoaded', () => {
	const PhotonicWizard = () => {
		let photonicLastActiveScreen = 1;
		let photonicMustPost = false;
		let photonicPermittedGalleries = ['wp', 'default', 'flickr', 'smugmug', 'google', 'zenfolio', 'instagram'];
		let photonicShortcodeObjectEditor;
		let photonicParentShortcode = '';
		let photonicParentSelectionError;
		let photonicIFramePort;
		let isEditor = false, isMCE = false, isWidget = false, isBlock = false;
		let photonicActiveScreenElementMediaLibrary, photonicClickedNodeMediaLibrary;
		let startupConnectionError;

		// setWaitingVisibility(true);
		window.addEventListener('message', initializeWizardPort);

		function initializeWizardPort(event) {
			// Perform a secure port transfer
			if (event.ports[0] && event.origin === Photonic_Wizard_JS.safe_origin) {
				photonicIFramePort = event.ports[0];
				photonicIFramePort.onmessage = handleMessageFromParent;
			}
			else if (event.origin !== Photonic_Wizard_JS.safe_origin) {
				startupConnectionError = '<strong>ERROR: </strong>Photonic wizard failed to initiate communication with main page. <ul><li>Main window is on ' + event.origin + '</li><li>Wizard is in ' + Photonic_Wizard_JS.safe_origin + '</li></ul>';
				console.error('Photonic failed to initialize communication. Main window is in ' + event.origin + ', Wizard is in ' + Photonic_Wizard_JS.safe_origin);
				if (startupConnectionError) {
					document.querySelector('.photonic-editor-info').innerHTML += '<div>' + startupConnectionError + '</div>';
				}
			}
		}

		function handleMessageFromParent(event) {
			if (event.data.type === 'photonicShortcode' || event.data.type === 'photonicMCENode' || event.data.type === 'photonicWidget' || event.data.type === 'photonicBlock') {
				setParentShortcode(event);
			}
			else if (event.data.type === 'photonicReceiveMediaLibrarySelections') {
				receiveMediaLibrarySelections(event.data.selection, event.data.options);
			}
		}

		function setParentShortcode(event) {
			let selection = event.data.object;
			// Selection = null if something went wrong
			// Selection = shortcode object if valid shortcode
			// Selection = null shortcode object and error, if not a valid shortcode
			// Selection = '' if nothing was selected
			if (selection !== null && selection.shortcode !== undefined && selection.shortcode !== null) {
				if (selection.shortcode.content !== undefined) { // Selection is a valid shortcode
					photonicParentShortcode = selection.shortcode.content;
					photonicShortcodeObjectEditor = selection.shortcode;
				}
				else {
					photonicParentShortcode = selection.shortcode; // Selection = ''
				}
			}
			else if (selection !== null && selection.error !== undefined) {
				photonicParentSelectionError = selection.error;
			}

			isEditor = event.data.type === 'photonicShortcode';
			isWidget = event.data.type === 'photonicWidget';
			isBlock = event.data.type === 'photonicBlock';
			isMCE = event.data.type === 'photonicMCENode';

			photonicIFramePort.postMessage(event.data);
			photonicIFramePort.postMessage({type: 'photonicAddTBClass'});

			wizardLogic(1);
		}

		function post(url, args, callback) {
			const xhr = new XMLHttpRequest();
			xhr.open('POST', url);
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4) {
					if (xhr.status === 200) {
						const data = xhr.responseText;
						callback(data);
					}
				}
			};
			let form = new FormData();
			const parameters = new URLSearchParams(args);
			for (const [key, value] of parameters.entries()) {
				form.append(key, value);
			}
			xhr.send(form);
		}

		function getElement(value) {
			const parser = new DOMParser();
			const doc = parser.parseFromString(value, 'text/html');
			return doc.body;
		}

		function log(value) {
			if (console !== undefined && Photonic_Wizard_JS.debug_on !== '0' && Photonic_Wizard_JS.debug_on !== '') {
				console.log(value);
			}
		}

		const postFlowData = (activeScreen, nextScreen, activeScreenElement, screenParameters, parameters) => {
			post(Photonic_Wizard_JS.ajaxurl, parameters, data => {
				let ret = getElement(data);

				if (ret.querySelector('.photonic-flow-error')) {
					if (document.querySelector('.photonic-flow-error')) {
						document.querySelector('.photonic-flow-error').remove();
					}
					document.querySelector('.photonic-flow-screen[data-screen="' + activeScreen + '"]').before(ret.querySelector('.photonic-flow-error'));
					activeScreenElement.setAttribute('data-submitted', '');
					log('Parameters causing failure: activeScreen = ' + activeScreen + ', nextScreen = ' + nextScreen);
					log(screenParameters);
					log(parameters);
				}
				else {
					if (document.querySelector('.photonic-flow-error')) {
						document.querySelector('.photonic-flow-error').style.display = 'none';
					}

					let forceScreen = ret.querySelector('input[name="force_next_screen"]');
					if (forceScreen) {
						if (parseInt(forceScreen.value, 10) > -1) {
							nextScreen = parseInt(forceScreen.value, 10);
						}
					}

					let nextScreenContent = document.querySelector('.photonic-flow-screen[data-screen="' + nextScreen + '"]');
					nextScreenContent.replaceChildren();
					nextScreenContent.insertAdjacentHTML('beforeend', data);

					wizardLogic(nextScreen);
					if (nextScreen <= 3) {
						const existing = document.querySelector('input[name="existing_selection"]') ? document.querySelector('input[name="existing_selection"]').value : undefined;
						const selection = document.querySelector('input[name="selected_data"]');
						const passworded = document.querySelector('input[name="selection_passworded"]');
						if (existing !== undefined && existing !== '') {
							selection.value = existing;
						}
						else {
							selection.value = '';
							passworded.value = '';
						}
					}
					photonicLastActiveScreen = nextScreen;
					activeScreenElement.setAttribute('data-submitted', screenParameters);
					photonicMustPost = false;
				}
				setWaitingVisibility(false);
			});
		};

		function receiveMediaLibrarySelections(selected_data, options) {
			document.querySelector('input[name="selected_data"]').value = selected_data

			const form = document.getElementById('photonic-flow');
			const formData = new FormData(form);
			let formParameters = new URLSearchParams(formData).toString();
			formParameters += ((formParameters === '') ? '' : '&') + 'action=photonic_wizard_next_screen&screen=' + (options.activeScreen) + '&_ajax_nonce=' + photonicClickedNodeMediaLibrary.getAttribute('data-photonic-nonce');

			postFlowData(options.activeScreen, options.nextScreen, photonicActiveScreenElementMediaLibrary, options.screenParameters, formParameters);

			if (selected_data !== '') {
				wizardLogic(options.nextScreen);
			}
		}

		const initializeWPMediaLibrary = (activeScreen, nextScreen, activeScreenElement, screenParameters, clicked) => {
			let mode = document.querySelector('select[name="display_type"]').value;
			if (mode === '') {
				alert(Photonic_Wizard_JS.error_mandatory);
			}
			else {
				mode = mode === 'single-photo' ? 'single' : 'add';

				// These 2 are needed as we cannot pass elements to postMessage -->
				photonicActiveScreenElementMediaLibrary = activeScreenElement;
				photonicClickedNodeMediaLibrary = clicked;
				// <--

				photonicIFramePort.postMessage({
					type: 'photonicInitializeMediaLibrary',
					mediaOptions: {
						multiple: mode,
						title: Photonic_Wizard_JS.media_library_title,
						library: {type: 'image'},
						button: {text: Photonic_Wizard_JS.media_library_button}
					},
					photonicOptions: {
						shortcodeTag: Photonic_Wizard_JS.shortcode,
						activeScreen: activeScreen,
						nextScreen: nextScreen,
						screenParameters: screenParameters,
						currentShortcode: photonicParentShortcode,
						selectedIds: document.querySelector('input[name="selected_data"]').value,
						isBlock: isBlock
					}
				});
			}
		};

		const checkCondition = (conditions) => {
			let conditionMet = true;
			Object.entries(conditions).forEach(([key, condition]) => {
				const keyField = document.querySelector('input[type="radio"][name="' + key + '"]:checked, select[name="' + key + '"], input[type="text"][name="' + key + '"], input[type="hidden"][name="' + key + '"]');
				if (keyField === null) {
					conditionMet = false;
				}
				else {
					conditionMet = conditionMet && condition.includes(keyField.value);
				}
			});
			return conditionMet;
		};

		const updateSelection = (clicked) => {
			const parent = clicked.closest('.photonic-flow-selector-container');
			let selection = [];

			parent.querySelectorAll('.photonic-flow-selector.selected .photonic-flow-selector-inner').forEach(item => {
				selection.push(item.getAttribute('data-photonic-selection-id'));
			});

			selection = selection.join();
			let selectorFor = parent.getAttribute('data-photonic-flow-selector-for');
			photonicMustPost = true;
			document.querySelector('input[name="' + selectorFor + '"]').value = selection;
		};

		const showConditionalFieldValues = (sibling) => {
			const siblingFieldValues = sibling.querySelectorAll('input[type="radio"], option');
			siblingFieldValues.forEach(input => {
				if (input.getAttribute('data-photonic-option-condition')) {
					const conditionMet = checkCondition(JSON.parse(input.getAttribute('data-photonic-option-condition')));
					if (conditionMet) {
						if (input.type === 'radio') {
							input.closest('.photonic-flow-field-radio').style.display = 'block';
						}
						else {
							input.style.display = 'block';
						}
					}
					else {
						if (input.type === 'radio') {
							input.checked = false;
							input.closest('.photonic-flow-field-radio').style.display = 'none';
						}
						else {
							input.style.display = 'none';
						}
					}
				}
			});
		};

		// From https://developer.mozilla.org/en-US/docs/Glossary/Base64#solution_2_%E2%80%93_rewriting_atob_and_btoa_using_typedarrays_and_utf-8, to handle https://wordpress.org/support/topic/set-load-more-button-default/ ...

		/*\
		|*|
		|*|  Base64 / binary data / UTF-8 strings utilities
		|*|
		|*|  https://developer.mozilla.org/en-US/docs/Web/JavaScript/Base64_encoding_and_decoding
		|*|
		\*/

		/* Array of bytes to Base64 string decoding */
		const photonicB64ToUint6 = (nChr) => {
			return nChr > 64 && nChr < 91 ?
				nChr - 65
				: nChr > 96 && nChr < 123 ?
					nChr - 71
					: nChr > 47 && nChr < 58 ?
						nChr + 4
						: nChr === 43 ?
							62
							: nChr === 47 ?
								63
								:
								0;
		};

		const photonicBase64DecToArr = (sBase64, nBlocksSize) => {
			let
				sB64Enc = sBase64.replace(/[^A-Za-z0-9\+\/]/g, ""), nInLen = sB64Enc.length,
				nOutLen = nBlocksSize ? Math.ceil((nInLen * 3 + 1 >> 2) / nBlocksSize) * nBlocksSize : nInLen * 3 + 1 >> 2,
				taBytes = new Uint8Array(nOutLen);

			for (let nMod3, nMod4, nUint24 = 0, nOutIdx = 0, nInIdx = 0; nInIdx < nInLen; nInIdx++) {
				nMod4 = nInIdx & 3;
				nUint24 |= photonicB64ToUint6(sB64Enc.charCodeAt(nInIdx)) << 6 * (3 - nMod4);
				if (nMod4 === 3 || nInLen - nInIdx === 1) {
					for (nMod3 = 0; nMod3 < 3 && nOutIdx < nOutLen; nMod3++, nOutIdx++) {
						taBytes[nOutIdx] = nUint24 >>> (16 >>> nMod3 & 24) & 255;
					}
					nUint24 = 0;

				}
			}

			return taBytes;
		};

		/* Base64 string to array encoding */
		const photonicUint6ToB64 = (nUint6) => {

			return nUint6 < 26 ?
				nUint6 + 65
				: nUint6 < 52 ?
					nUint6 + 71
					: nUint6 < 62 ?
						nUint6 - 4
						: nUint6 === 62 ?
							43
							: nUint6 === 63 ?
								47
								:
								65;

		};

		const photonicBase64EncArr = (aBytes) => {
			let nMod3 = 2, sB64Enc = "";

			for (let nLen = aBytes.length, nUint24 = 0, nIdx = 0; nIdx < nLen; nIdx++) {
				nMod3 = nIdx % 3;
				if (nIdx > 0 && (nIdx * 4 / 3) % 76 === 0) {
					sB64Enc += "\r\n";
				}
				nUint24 |= aBytes[nIdx] << (16 >>> nMod3 & 24);
				if (nMod3 === 2 || aBytes.length - nIdx === 1) {
					sB64Enc += String.fromCodePoint(photonicUint6ToB64(nUint24 >>> 18 & 63), photonicUint6ToB64(nUint24 >>> 12 & 63), photonicUint6ToB64(nUint24 >>> 6 & 63), photonicUint6ToB64(nUint24 & 63));
					nUint24 = 0;
				}
			}

			return sB64Enc.substr(0, sB64Enc.length - 2 + nMod3) + (nMod3 === 2 ? '' : nMod3 === 1 ? '=' : '==');

		};

		/* UTF-8 array to JS string and vice versa */
		const photonicUTF8ArrToStr = (aBytes) => {
			let sView = "";

			for (let nPart, nLen = aBytes.length, nIdx = 0; nIdx < nLen; nIdx++) {
				nPart = aBytes[nIdx];
				sView += String.fromCodePoint(
					nPart > 251 && nPart < 254 && nIdx + 5 < nLen ? /* six bytes */
						/* (nPart - 252 << 30) may be not so safe in ECMAScript! So…: */
						(nPart - 252) * 1073741824 + (aBytes[++nIdx] - 128 << 24) + (aBytes[++nIdx] - 128 << 18) + (aBytes[++nIdx] - 128 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128
						: nPart > 247 && nPart < 252 && nIdx + 4 < nLen ? /* five bytes */
							(nPart - 248 << 24) + (aBytes[++nIdx] - 128 << 18) + (aBytes[++nIdx] - 128 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128
							: nPart > 239 && nPart < 248 && nIdx + 3 < nLen ? /* four bytes */
								(nPart - 240 << 18) + (aBytes[++nIdx] - 128 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128
								: nPart > 223 && nPart < 240 && nIdx + 2 < nLen ? /* three bytes */
									(nPart - 224 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128
									: nPart > 191 && nPart < 224 && nIdx + 1 < nLen ? /* two bytes */
										(nPart - 192 << 6) + aBytes[++nIdx] - 128
										: /* nPart < 127 ? */ /* one byte */
										nPart
				);
			}

			return sView;

		};

		const photonicStrToUTF8Arr = (sDOMStr) => {
			let aBytes, nChr, nStrLen = sDOMStr.length, nArrLen = 0;

			/* mapping… */

			for (let nMapIdx = 0; nMapIdx < nStrLen; nMapIdx++) {
				nChr = sDOMStr.codePointAt(nMapIdx);

				if (nChr > 65536) {
					nMapIdx++;
				}

				nArrLen += nChr < 0x80 ? 1 : nChr < 0x800 ? 2 : nChr < 0x10000 ? 3 : nChr < 0x200000 ? 4 : nChr < 0x4000000 ? 5 : 6;
			}

			aBytes = new Uint8Array(nArrLen);

			/* transcription… */

			for (let nIdx = 0, nChrIdx = 0; nIdx < nArrLen; nChrIdx++) {
				nChr = sDOMStr.codePointAt(nChrIdx);
				if (nChr < 128) {
					/* one byte */
					aBytes[nIdx++] = nChr;
				}
				else if (nChr < 0x800) {
					/* two bytes */
					aBytes[nIdx++] = 192 + (nChr >>> 6);
					aBytes[nIdx++] = 128 + (nChr & 63);
				}
				else if (nChr < 0x10000) {
					/* three bytes */
					aBytes[nIdx++] = 224 + (nChr >>> 12);
					aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
					aBytes[nIdx++] = 128 + (nChr & 63);
				}
				else if (nChr < 0x200000) {
					/* four bytes */
					aBytes[nIdx++] = 240 + (nChr >>> 18);
					aBytes[nIdx++] = 128 + (nChr >>> 12 & 63);
					aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
					aBytes[nIdx++] = 128 + (nChr & 63);
					nChrIdx++;
				}
				else if (nChr < 0x4000000) {
					/* five bytes */
					aBytes[nIdx++] = 248 + (nChr >>> 24);
					aBytes[nIdx++] = 128 + (nChr >>> 18 & 63);
					aBytes[nIdx++] = 128 + (nChr >>> 12 & 63);
					aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
					aBytes[nIdx++] = 128 + (nChr & 63);
					nChrIdx++;
				}
				else /* if (nChr <= 0x7fffffff) */ {
					/* six bytes */
					aBytes[nIdx++] = 252 + (nChr >>> 30);
					aBytes[nIdx++] = 128 + (nChr >>> 24 & 63);
					aBytes[nIdx++] = 128 + (nChr >>> 18 & 63);
					aBytes[nIdx++] = 128 + (nChr >>> 12 & 63);
					aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
					aBytes[nIdx++] = 128 + (nChr & 63);
					nChrIdx++;
				}
			}

			return aBytes;

		};

		const wizardLogic = (screen) => {
			const existing = document.querySelector('input[name="photonic-editor-shortcode"]').getAttribute('value');
			const existingBlock = document.querySelector('input[name="photonic-editor-json"]').getAttribute('value');

			if (screen === 1) {
				if (existing === undefined || existing === null || existing === '') {
					let shortcode = photonicShortcodeObjectEditor, attributes, type;

					if (isEditor || isWidget || isMCE) {
						if (typeof shortcode !== 'undefined' && shortcode !== null) {
							let scParameter = photonicBase64EncArr(photonicStrToUTF8Arr(JSON.stringify(shortcode))); // To handle unicode in "more"; see https://developer.mozilla.org/en-US/docs/Glossary/Base64#solution_1_%E2%80%93_escaping_the_string_before_encoding_it
							scParameter = scParameter.replace(/\n/g, '');
							scParameter = scParameter.replace(/^=+|=+$/g, '');
							attributes = shortcode.shortcode.attrs.named;
							if (attributes['type'] !== undefined && photonicPermittedGalleries.indexOf(attributes['type']) !== -1) {
								type = attributes['type'];
							}
							else if ((attributes['type'] === undefined && Photonic_Wizard_JS.shortcode !== 'gallery') || attributes['style'] !== undefined) {
								type = 'wp';
							}

							if (type !== undefined) {
								if (type === 'google') {
									document.querySelector('.photonic-editor-info').innerHTML = '<div>' + Photonic_Wizard_JS.info_editor_google_shortcode + '</div>';
								}
								else {
									document.querySelector('[name="photonic-editor-shortcode-raw"]').value = scParameter;
									document.querySelector('[name="photonic-editor-shortcode"]').value = photonicParentShortcode;
									document.querySelector('[data-photonic-selection-id="' + type + '"]').click();
									document.querySelector('.photonic-editor-info').innerHTML = '';
								}
							}
							else {
								document.querySelector('.photonic-editor-info').innerHTML = '<div>' + Photonic_Wizard_JS.info_editor_not_shortcode + '</div>';
							}
						}
						else if (photonicParentSelectionError !== undefined) {
							document.querySelector('.photonic-editor-info').innerHTML = '<div>' + Photonic_Wizard_JS.info_editor_not_shortcode + '</div>';
						}
					}
					else if (isBlock) { // Gutenberg
						document.querySelector('[name="photonic-gutenberg-active"]').value = 1;

						if (photonicParentShortcode !== undefined && photonicParentShortcode !== '') {
							shortcode = photonicParentShortcode;
							attributes = JSON.parse(shortcode);

							if (attributes.type !== undefined && photonicPermittedGalleries.indexOf(attributes.type) !== -1) {
								type = attributes.type;
							}
							else if (attributes.style !== undefined) {
								type = 'wp';
							}

							if (type !== undefined) {
								document.querySelector('[name="photonic-editor-json"]').value = shortcode;
								document.querySelector('[data-photonic-selection-id="' + type + '"]').click();
								document.querySelector('.photonic-editor-info').replaceChildren();
							}
						}
						else {
							document.querySelector('.photonic-editor-info').innerHTML = '<div>' + Photonic_Wizard_JS.info_editor_block_select + '</div>';
						}
					}
				}
				setWaitingVisibility(false);
			}

			if (screen === 6) {
				if ((existing === undefined || existing === '') && (existingBlock === undefined || existingBlock === '')) {
					document.getElementById('photonic-nav-next').innerHTML = Photonic_Wizard_JS.insert_gallery;
				}
				else {
					document.getElementById('photonic-nav-next').innerHTML = Photonic_Wizard_JS.update_gallery;
				}
			}
			else {
				document.getElementById('photonic-nav-next').innerHTML = 'Next';
			}

			document.querySelectorAll('.photonic-flow-screen').forEach(screen => {
				screen.style.display = 'none';
			});

			const activeScreen = document.querySelector('.photonic-flow-screen[data-screen="' + screen + '"]');
			const fieldSequences = activeScreen.querySelectorAll('.photonic-flow-field[data-photonic-flow-sequence="1"]');
			const displayType = activeScreen.querySelector('select[name="display_type"]');
			const popupType = activeScreen.querySelector('select[name="popup"]');

			fieldSequences.forEach(fieldSequence => {
				const group = fieldSequence.getAttribute('data-photonic-flow-sequence-group');

				document.querySelectorAll('.photonic-flow-field[data-photonic-flow-sequence-group="' + group + '"]').forEach((fieldContainer, idx) => {
					let field = fieldContainer.querySelector('input, select');
					let isRadio = false;

					const fieldName = field.getAttribute('name');
					let fieldSelection = document.querySelector('input[type="radio"][name="' + fieldName + '"]:checked') || document.querySelector('input[type="text"][name="' + fieldName + '"], select[name="' + fieldName + '"]');
					const fieldValue = fieldSelection ? fieldSelection.value : undefined;

					if (idx !== 0 && (fieldValue === '' || fieldValue === undefined)) {
						fieldContainer.style.display = 'none';
					}

					// This has to happen after "fieldValue" is set
					if (field.tagName === 'INPUT' && field.type === 'radio') {
						field = field.closest('.photonic-flow-field-radio-group');
						isRadio = true;
					}


					const siblings = fieldContainer.parentNode.children;
					let sequence = parseInt(fieldContainer.getAttribute('data-photonic-flow-sequence'), 10);

					field.addEventListener('change', () => {
						if (isRadio) {
							fieldSelection = document.querySelector('input[type="radio"][name="' + fieldName + '"]:checked');
						}
						const error = document.querySelector('.photonic-flow-error');
						if (error) {
							error.style.display = 'none';
						}

						if ((!isRadio && field.value !== '') || (isRadio && fieldSelection && fieldSelection.value !== undefined)) {
							Array.from(siblings).forEach(sibling => {
								let currSequence = parseInt(sibling.getAttribute('data-photonic-flow-sequence'), 10);
								if (currSequence > sequence) {
									if (sibling.getAttribute('data-photonic-condition')) {
										const conditionMet = checkCondition(JSON.parse(sibling.getAttribute('data-photonic-condition')));
										sibling.style.display = conditionMet ? 'block' : 'none';
									}
									else {
										fadeIn(sibling);
									}
									showConditionalFieldValues(sibling);
								}
							});
						}
						else {
							Array.from(siblings).forEach(sibling => {
								let currSequence = parseInt(sibling.getAttribute('data-photonic-flow-sequence'), 10);
								if (currSequence > sequence) {
									fadeOut(sibling);
								}
							});
						}
					});
				});
			});

			activeScreen.querySelectorAll('[data-photonic-condition] input, [data-photonic-condition] select').forEach(input => {
				const fieldContainer = input.closest('.photonic-flow-field');
				const sequences = input.closest('[data-photonic-flow-sequence]');
				if (fieldContainer && !sequences) {
					if (fieldContainer.getAttribute('data-photonic-condition') && fieldContainer.getAttribute('data-photonic-condition') !== '') {
						const conditionMet = checkCondition(fieldContainer.getAttribute('data-photonic-condition'));
						fieldContainer.style.display = conditionMet ? 'block' : 'none';
					}
				}
			});

			if (screen === 2) {
				// One exception to the above ...
				if (displayType && displayType.value !== '') {
					const fieldContainer = document.querySelector('[name="for"]') ? document.querySelector('[name="for"]').closest('.photonic-flow-field') : undefined;
					if (fieldContainer) {
						showConditionalFieldValues(fieldContainer);
						fadeIn(fieldContainer);
					}
				}
			}
			else if (screen === 5) {
				// ... And another exception
				if (popupType && popupType.value !== '' && popupType.value !== 'hide') {
					const managedFields = document.querySelectorAll('[name="photo_count"], [name="photo_more"], [name="photo_layout"]');
					managedFields.forEach(managedField => {
						const fieldContainer = managedField.closest('.photonic-flow-field');
						showConditionalFieldValues(fieldContainer);
						fadeIn(fieldContainer);
					});
				}
			}

			activeScreen.style.display = 'block';
			if (screen === 1) {
				document.querySelector('.photonic-flow-navigation a.previous').classList.add('disabled');
			}
			else {
				document.querySelector('.photonic-flow-navigation a.previous').classList.remove('disabled');
			}
		};

		function fadeIn(el) {
			if (!el.classList.contains('fade-in')) {
				el.style.display = 'block';
				el.style.visibility = 'visible';
				el.classList.add('fade-in');
			}
		}

		function fadeOut(el, duration) {
			let s = el.style,
				step = 25 / (duration || 500);
			s.opacity = s.opacity || 1;
			(function fade() {
				s.opacity -= step;
				if (s.opacity < 0) {
					s.display = "none";
					el.classList.remove('fade-in');
				}
				else {
					setTimeout(fade, 25);
				}
			})();
		}

		function setWaitingVisibility(visible) {
			const waiting = document.querySelector('.photonic-waiting');
			waiting.style.display = visible ? 'block' : 'none';
		}

		document.querySelector('.photonic-flow-navigation a.disabled').addEventListener('click', (e) => {
			e.preventDefault();
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('.photonic-flow-navigation a')) {
				return;
			}

			let clicked = e.target.closest('.photonic-flow-navigation a');
			if (!clicked.classList.contains('disabled')) {
				e.preventDefault();
				setWaitingVisibility(true);
				let activeScreen;
				let flowScreens = document.querySelectorAll('.photonic-flow-screen');
				flowScreens.forEach(f => {
					if (f.checkVisibility()) {
						activeScreen = parseInt(f.getAttribute('data-screen'), 10);
					}
				});

				let nextScreen = activeScreen + 1;
				let previousScreen = activeScreen - 1;

				const form = document.getElementById('photonic-flow');
				const formData = new FormData(form);
				let formParameters = new URLSearchParams(formData).toString();

				const activeScreenElement = document.querySelector('.photonic-flow-screen[data-screen="' + activeScreen + '"]');
				if (clicked.classList.contains('next')) {
					let shortcode = activeScreenElement.querySelector('#photonic_shortcode');
					let submission = activeScreenElement.getAttribute('data-submitted');

					let screenFormFields = new FormData();
					activeScreenElement.querySelectorAll('input, select').forEach(input => {
						// Only serialize fields with names that aren't disabled
						if (input.name && !input.disabled) {
							// Handle checkboxes and radios only if checked
							if (['checkbox', 'radio'].includes(input.type)) {
								if (input.checked) {
									screenFormFields.append(input.name, input.value);
								}
							}
							else {
								screenFormFields.append(input.name, input.value);
							}
						}
					});
					let screenParameters = new URLSearchParams(screenFormFields).toString();

					formParameters += ((formParameters === '') ? '' : '&') + 'action=photonic_wizard_next_screen&screen=' + activeScreen + '&_ajax_nonce=' + clicked.getAttribute('data-photonic-nonce');

					// Make AJAX call if we are on the last screen, or if the current screen's parameters have changed since the last time.
					// Otherwise just get the previously fetched screen. This saves a server call, and also helps preserve screen changes not sent to the back-end.
					if (shortcode) {
						if (isMCE) { // TinyMCE Editor
							photonicIFramePort.postMessage({type: 'photonicUpdateGallery', html: shortcode.textContent});
						}
						else if (isBlock) { // Gutenberg
							photonicIFramePort.postMessage({type: 'photonicUpdateGallery', props: shortcode.value});
						}
						else if (isWidget) {
							photonicIFramePort.postMessage({type: 'photonicUpdateGallery', html: shortcode.textContent});
						}
						else {
							photonicIFramePort.postMessage({type: 'photonicUpdateGallery', html: shortcode.textContent});
						}
					}
					else if (activeScreen === 2
						&& document.querySelector('input[name="provider"]') && document.querySelector('input[name="provider"]').value === 'wp'
						&& document.querySelector('select[name="display_type"]') && document.querySelector('select[name="display_type"]').value === 'multi-photo') {
						initializeWPMediaLibrary(activeScreen, nextScreen, activeScreenElement, screenParameters, clicked);
						photonicIFramePort.postMessage({type: 'photonicOpenMediaLibrary'});
						setWaitingVisibility(false);
					}
					else if (activeScreen === photonicLastActiveScreen || submission !== screenParameters || photonicMustPost) {
						postFlowData(activeScreen, nextScreen, activeScreenElement, screenParameters, formParameters);
					}
					else {
						wizardLogic(nextScreen);
						setWaitingVisibility(false);
					}
				}
				else if (clicked.classList.contains('previous')) {
					let forceScreen = activeScreenElement.querySelector('input[name="force_previous_screen"]');
					if (forceScreen && parseInt(forceScreen.value, 10) > -1) {
						previousScreen = parseInt(forceScreen.value, 10);
					}

					wizardLogic(previousScreen);
					setWaitingVisibility(false);
				}
			}
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('.photonic-gallery a')) {
				return;
			}
			e.preventDefault();
			document.querySelectorAll('.photonic-gallery a').forEach(icon => {
				icon.classList.remove('selected');
			});
			const clicked = e.target.closest('.photonic-gallery a');
			clicked.classList.add('selected');
			document.getElementById('provider').value = clicked.getAttribute('data-provider');
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('.photonic-flow-selector')) {
				return;
			}
			e.preventDefault();

			const clicked = e.target.closest('.photonic-flow-selector');
			const container = clicked.closest('.photonic-flow-selector-container');
			const selectionMode = container.getAttribute('data-photonic-flow-selector-mode');

			if (selectionMode === 'none') {
				return;
			}
			else if (selectionMode === 'single' || selectionMode === 'single-no-plus') {
				container.querySelectorAll('.photonic-flow-selector').forEach(selector => {
					selector.classList.remove('selected');
				});
				container.querySelectorAll('.photonic-flow-selector .dashicons').forEach(selector => {
					selector.remove();
				});

				if (container.getAttribute('data-photonic-flow-selector-for') === 'selected_data') {
					const selectionPassworded = document.querySelector('input[name="selection_passworded"]');

					if (clicked.classList.contains('passworded')) {
						if (selectionPassworded.value === '') {
							photonicMustPost = true;
						}
						selectionPassworded.value = '1';
					}
					else {
						if (selectionPassworded.value === '1' || selectionPassworded.value === 1) {
							photonicMustPost = true;
						}
						selectionPassworded.value = '';
					}
				}
			}

			if (selectionMode === 'multi') {
				clicked.classList.add('selected');
				addDashIcon(clicked);
			}
			else if (selectionMode === 'single-no-plus' || selectionMode === 'single') {
				clicked.classList.add('selected');
			}
			updateSelection(clicked);
		});

		function addDashIcon(thumbnail) {
			const icon = document.createElement("a");
			icon.classList.add('dashicons', 'dashicons-plus');
			icon.href = '#';
			thumbnail.append(icon);
			icon.addEventListener('click', ie => { // For some reason, doing this on document.addEventListener is unable to remove the dashicons.
				ie.preventDefault();
				ie.stopPropagation();
				thumbnail.classList.remove('selected');
				icon.remove();
				updateSelection(thumbnail);
			});

			['mouseenter', 'mouseleave'].forEach(eventType => {
				icon.addEventListener(eventType, me => {
					me.preventDefault();
					me.stopPropagation();
					icon.classList.toggle('dashicons-plus');
					icon.classList.toggle('dashicons-minus');
				});
			});
		}

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('.photonic-mark')) {
				return;
			}
			e.preventDefault();
			const clicked = e.target.closest('.photonic-mark');
			const markFor = clicked.getAttribute('data-photonic-mark-for');
			const thumbnails = document.querySelectorAll('.photonic-flow-selector-container[data-photonic-flow-selector-for="' + markFor + '"] .photonic-flow-selector');
			let selection = '';
			thumbnails.forEach(thumbnail => {
				if (clicked.classList.contains('photonic-mark-all') && !thumbnail.classList.contains('selected')) {
					thumbnail.classList.add('selected');
					addDashIcon(thumbnail);
					selection += thumbnail.querySelector('.photonic-flow-selector-inner').getAttribute('data-photonic-selection-id') + ',';
				}
				else if (clicked.classList.contains('photonic-mark-none')) {
					thumbnail.classList.remove('selected');
					if (thumbnail.querySelector('.dashicons')) {
						thumbnail.querySelector('.dashicons').remove();
					}
				}
			});
			if (selection !== '') {
				selection = selection.replace(/^,+|,+$/g, '');
			}
			document.querySelector('input[name="' + markFor + '"]').value = selection;
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('a.photonic-add-date-filter')) {
				return;
			}
			e.preventDefault();
			const clicked = e.target.closest('a.photonic-add-date-filter');

			const dateFilterField = clicked.getAttribute('data-photonic-add-date');
			const list = document.querySelector('ol[data-photonic-date-filter="' + dateFilterField + '"]');
			const dateFilterCount = parseInt(list.getAttribute('data-photonic-filter-count'), 10);
			const currentCount = list.children.length;
			const listItem = document.createElement("li");
			const div = document.createElement('div');
			div.classList.add('photonic-single-date');
			const parts = ['Year', 'Month', 'Date'];
			const texts = ['Year (0 - 9999)', 'Month (0 - 12)', 'Date (0 - 31)'];
			parts.forEach((part, j) => {
				div.insertAdjacentHTML('beforeend',
					"<label class='photonic-date-filter'>" +
					part.substring(0, 1) +
					"<input type='text' class='photonic-date-" + part.toLowerCase() + "' name='" + dateFilterField + "_" + part.toLowerCase() + "[]' aria-describedby='date_filter_" + dateFilterField + "_" + currentCount + "_" + part.toLowerCase() + "-hint'/>" +
					"<div class='photonic-flow-hint' role='tooltip' id='date_filter_" + dateFilterField + "_" + currentCount + "_" + part.toLowerCase() + "-hint'>" + texts[j] + "</div>" +
					"</label>"
				);
			});
			listItem.append(div);
			listItem.insertAdjacentHTML('beforeend', "<a href='#' class='photonic-remove-date-filter' title='Remove filter'><span class=\"dashicons dashicons-no\"> </span></a>");
			list.append(listItem);
			if (list.children.length === dateFilterCount) {
				clicked.style.display = 'none';
			}
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('a.photonic-add-date-range-filter')) {
				return;
			}
			e.preventDefault();
			const clicked = e.target.closest('a.photonic-add-date-range-filter');

			const dateFilterField = clicked.getAttribute('data-photonic-add-date-range');
			const list = document.querySelector('ol[data-photonic-date-range-filter="' + dateFilterField + '"]');
			const dateFilterCount = parseInt(list.getAttribute('data-photonic-filter-count'), 10);
			const currentCount = list.children.length;
			const listItem = document.createElement('li');
			const parts = ['Year', 'Month', 'Date'];
			const range_parts = ['start', 'finish'];
			const texts = ['Year (0 - 9999)', 'Month (0 - 12)', 'Date (0 - 31)'];

			range_parts.forEach(range_part => {
				const div = document.createElement('div');
				div.classList.add('photonic-single-date');
				parts.forEach((part, counter) => {
					div.insertAdjacentHTML('beforeend',
						"<label class='photonic-date-filter'>" +
						part.substr(0, 1) +
						"<input type='text' class='photonic-date-" + part.toLowerCase() + "' name='" + dateFilterField + "_" + range_part + "_" + part.toLowerCase() + "[]' aria-describedby='date_range_filter_" + dateFilterField + "_" + currentCount + "_" + range_part + "_" + part.toLowerCase() + "-hint'/>" +
						"<div class='photonic-flow-hint' role='tooltip' id='date_range_filter_" + dateFilterField + "_" + currentCount + "_" + range_part + "_" + part.toLowerCase() + "-hint'>" + texts[counter] + "</div>" +
						"</label>"
					);
				});
				listItem.append(div);
			});

			listItem.insertAdjacentHTML('beforeend', "<a href='#' class='photonic-remove-date-range-filter' title='Remove filter'><span class=\"dashicons dashicons-no\"> </span></a>");
			list.append(listItem);
			if (list.children.length === dateFilterCount) {
				clicked.style.display = 'none';
			}
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('a.photonic-remove-date-filter')) {
				return;
			}
			e.preventDefault();
			const clicked = e.target.closest('a.photonic-remove-date-filter');

			const listItem = clicked.closest('li');
			const list = listItem.parentElement;
			const dateFilterField = list.getAttribute('data-photonic-date-filter');
			const dateFilterCount = parseInt(list.getAttribute('data-photonic-filter-count'), 10);
			let addButton = document.querySelector("a[data-photonic-add-date='" + dateFilterField + "']");
			if (!addButton) {
				addButton = document.createElement('a');
				addButton.setAttribute('href', '#');
				addButton.setAttribute('data-photonic-add-date', dateFilterField);
				addButton.classList.add('photonic-add-date-filter');
				addButton.insertAdjacentHTML('beforeend', "<span class=\"dashicons dashicons-plus-alt\"> </span> Add filter");

				list.after(addButton);
				addButton.style.display = 'none';
			}
			listItem.remove();
			if (list.children.length < dateFilterCount) {
				addButton.style.display = 'block';
			}
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('a.photonic-remove-date-range-filter')) {
				return;
			}
			e.preventDefault();
			const clicked = e.target.closest('a.photonic-remove-date-range-filter');
			const listItem = clicked.closest('li');
			const list = listItem.parentElement;
			const dateFilterField = list.getAttribute('data-photonic-date-range-filter');
			const dateFilterCount = parseInt(list.getAttribute('data-photonic-filter-count'), 10);

			let addButton = document.querySelector("a[data-photonic-add-date-range='" + dateFilterField + "']");
			if (!addButton) {
				addButton = document.createElement('a');
				addButton.setAttribute('href', '#');
				addButton.setAttribute('data-photonic-add-date-range', dateFilterField);
				addButton.classList.add('photonic-add-date-range-filter');
				addButton.insertAdjacentHTML('beforeend', "<span class=\"dashicons dashicons-plus-alt\"> </span> Add filter");

				list.after(addButton);
				addButton.style.display = 'none';
			}

			listItem.remove();
			if (list.children.length < dateFilterCount) {
				addButton.style.display = 'block';
			}
		});

		document.addEventListener('change', e => {
			if (!(e.target instanceof Element) || !e.target.closest('input[class^=photonic-date-]')) {
				return;
			}
			e.preventDefault();
			const changed = e.target.closest('input[class^=photonic-date-]');
			const container = changed.closest('ol');
			if (container) {
				const range = container.getAttribute('data-photonic-date-range-filter') !== undefined;
				const dates = container.children;
				let listMerge = [];

				dates.forEach(date => {
					let itemMerge = [];
					const dateFields = date.querySelectorAll('input');
					if (dateFields.length > 0) {
						itemMerge[itemMerge.length] = [];
					}
					if (dateFields.length > 3) {
						itemMerge[itemMerge.length] = [];
					}
					dateFields.forEach((d, i) => {
						const mod = Math.floor(i / 3);
						const div = i % 3;
						itemMerge[mod][div] = d.value === '' ? 0 : d.value;
					});
					itemMerge.forEach((d, i) => {
						itemMerge[i] = d.join('/');
					});
					listMerge.push(itemMerge.join('-'));
				});
				listMerge = listMerge.join();
				if (range) {
					document.querySelector('input[name="' + container.getAttribute('data-photonic-date-range-filter') + '"]').value = listMerge;
				}
				else {
					document.querySelector('input[name="' + container.getAttribute('data-photonic-date-filter') + '"]').value = listMerge;
				}
			}
		});

		document.addEventListener('click', e => {
			if (!(e.target instanceof Element) || !e.target.closest('a.photonic-flow-more')) {
				return;
			}
			e.preventDefault();

			const clicked = e.target.closest('a.photonic-flow-more');
			const container = clicked.closest('.photonic-flow-selector-container');
			const link = clicked.getAttribute('data-photonic-more-link');
			const existing = document.querySelector('input[name="photonic-editor-shortcode"]') ? document.querySelector('input[name="photonic-editor-shortcode"]').value : undefined;
			const existingBlock = document.querySelector('input[name="photonic-editor-json"]') ? document.querySelector('input[name="photonic-editor-json"]').value : undefined;

			let shortcode, attributes, albumFilter;

			if (existing && !isBlock) {
				if (photonicShortcodeObjectEditor !== undefined) {
					attributes = photonicShortcodeObjectEditor.shortcode.attrs.named;
					if (attributes['filter']) {
						albumFilter = attributes['filter'];
					}
				}
			}
			else if (existingBlock && isBlock) { // Gutenberg
				if (photonicParentShortcode !== undefined && photonicParentShortcode !== '') {
					shortcode = photonicParentShortcode;
					attributes = JSON.parse(shortcode);

					if (attributes.filter !== undefined) {
						albumFilter = attributes.filter;
					}
				}
			}

			if (link !== undefined && link !== '') {
				setWaitingVisibility(true);
				let parameters = [];
				parameters['action'] = 'photonic_wizard_more';
				parameters['provider'] = clicked.getAttribute('data-photonic-provider');
				parameters['display_type'] = clicked.getAttribute('data-photonic-display-type');
				parameters['url'] = encodeURIComponent(clicked.getAttribute('data-photonic-more-link'));

				if (albumFilter) {
					albumFilter = '&filter=' + albumFilter;
				}
				else {
					albumFilter = '';
				}
				parameters = 'action=photonic_wizard_more&provider=' + clicked.getAttribute('data-photonic-provider') + '&display_type=' + clicked.getAttribute('data-photonic-display-type') + '&url=' +
					btoa(clicked.getAttribute('data-photonic-more-link')) + albumFilter + '&_ajax_nonce=' + clicked.getAttribute('data-photonic-nonce');

				post(Photonic_Wizard_JS.ajaxurl, parameters, function (data) {
					clicked.style.display = 'none';
					if (clicked.closest('.photonic-more-wrapper')) {
						clicked.closest('.photonic-more-wrapper').remove();
					}
					const currentSelectors = container.querySelectorAll('.photonic-flow-selector');
					const lastItem = currentSelectors.item(currentSelectors.length - 1);

					if (lastItem) {
						lastItem.insertAdjacentHTML('afterend', data);
					}

					const search = document.getElementById('thumb-search');
					search.focus();
					search.blur();

					updateSelection(document.querySelector('.photonic-flow-selector:last-child'));

					setWaitingVisibility(false);
				});
			}
		});

		document.addEventListener('mouseover', e => {
			if (!(e.target instanceof Element) || !e.target.closest('input, select')) {
				return;
			}
			e.stopPropagation();
			const item = e.target;
			const tooltip = document.querySelector('#' + item.getAttribute('aria-describedby'));
			if (tooltip) {
				tooltip.setAttribute('aria-hidden', false);
				tooltip.style.display = 'block';
				fadeIn(tooltip);
			}
		});

		document.addEventListener('mouseout', e => {
			if (!(e.target instanceof Element) || !e.target.closest('input, select, .photonic-flow-hint')) {
				return;
			}
			e.stopPropagation();
			const item = e.target;
			const tooltipId = item.classList.contains('photonic-flow-hint') ? item.getAttribute('id') : '#' + item.getAttribute('aria-describedby');
			const tooltip = document.querySelector(tooltipId);
			if (tooltip && !document.querySelector(tooltipId + ':hover')) {
				tooltip.setAttribute('aria-hidden', true);
				tooltip.style.display = 'none';
			}
		});

		document.getElementById('photonic-flow').addEventListener('focusin', e => {
			if (!e.target.matches('#thumb-search')) {
				return;
			}

			const search = e.target;
			const images = document.querySelectorAll('.photonic-flow-selector-container img');
			const cache = [];

			images.forEach((image) => {
				cache.push({
					element: image,
					text: image.alt.trim().toLowerCase()
				});
			});

			function filter(e) {
				const query = e.target.value.trim().toLowerCase();
				cache.forEach(image => {
					let index = 0;
					if (query) {
						index = image.text.indexOf(query);
					}
					if (index === -1) {
						image.element.closest('.photonic-flow-selector').style.display = 'none';
					}
					else {
						image.element.closest('.photonic-flow-selector').style.display = 'inline-block';
					}
				});
			}

			search.addEventListener('input', filter);
			search.addEventListener('keyup', filter);
		});

		document.addEventListener('change', e => {
			if (!(e.target instanceof Element) || !e.target.matches('[name="selected_data"],[name="selection_passworded"]')) {
				return;
			}
			photonicMustPost = true;
		});
	}

	PhotonicWizard();
});
