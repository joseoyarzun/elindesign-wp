let photonicClickedNode;
(function($) {
	tinymce.PluginManager.add('photonic', function(editor, url) {
		const wizardButton = document.querySelector('#photonic-add-gallery');
		const nativeMediaLibrary = new PhotonicWPNativeUI(sendDataToWizard, getDataFromWizard);
		const mceTab = document.getElementById('content-tmce'), htmlTab = document.getElementById('content-html');
		let lastClickedTab = mceTab; // We are in the MCE Editor, so this must be the clicked tab.

		nativeMediaLibrary.waitForIFrame();

		function html(cls, data, type) {
			data = window.encodeURIComponent(data);
			return '<img src="' + tinymce.Env.transparentSrc + '" class="wp-media mceItem ' + cls + '" ' + 'data-wp-media="' + data + '" data-mce-resize="false" data-mce-placeholder="1" alt="" title="Photonic ' + type + ' gallery" />';
		}

		function restoreMediaShortcodes(content) {
			function getAttr(str, name) {
				name = new RegExp(name + '=\"([^\"]+)\"').exec(str);
				return name ? window.decodeURIComponent(name[1]) : '';
			}

			let newContent;
			newContent = content.replace(/(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function(match, image) {
				const data = getAttr(image, 'data-wp-media');
				if (data) {
					return '<p>' + data + '</p>';
				}

				return match;
			});
			return newContent;
		}

		editor.on('mouseup', function(event) {
			const dom = editor.dom,
				node = event.target;

			function unselect() {
				dom.removeClass(dom.select('img.wp-media-selected'), 'wp-media-selected');
			}

			if (node.nodeName === 'IMG' && dom.getAttrib(node, 'data-wp-media')) {
				// Don't trigger on right-click
				unselect();
			}
		});

		// Display gallery, audio or video instead of img in the element path
		editor.on('GetContent', function(event) {
			if (event.get) {
				event.content = restoreMediaShortcodes(event.content);
			}
		});

		// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
		editor.addCommand('Photonic_Gallery', function(ui, v) {
			const node = editor.selection.getNode();
			const type = v.type;

			const shortcode = wp.mce.views.getText(node);
			const shortcodeObj = wp.shortcode.next(Photonic_Admin_JS.shortcode, shortcode);
			const shortcodeAttr = shortcodeObj.shortcode.attrs.named;

			const template = (new DOMParser().parseFromString(document.getElementById('tmpl-photonic-editor-' + type).innerHTML.replace('<script type="text/html" id="tmpl-photonic-editor-' + type + '">', '').replace('</script>', ''), 'text/html')).body

			// First, set all the inputs and selects in the template to the shortcode values
			template.querySelectorAll('input').forEach(input => {
				if (shortcodeAttr[input.name] !== undefined) {
					template.querySelector('input[name="' + input.name + '"]').value = shortcodeAttr[input.name];
				}
				else if (input.getAttribute('alt_id') && shortcodeAttr[input.getAttribute('alt_id')] !== undefined) {
					template.querySelector('input[alt_id="' + input.getAttribute('alt_id') + '"]').value = shortcodeAttr[input.getAttribute('alt_id')];
				}
			});

			template.querySelectorAll('select').forEach(select => {
				if (shortcodeAttr[select.name] !== undefined) {
					template.querySelector('select[name="' + select.name + '"]').value = shortcodeAttr[select.name];
				}
				else if (select.getAttribute('alt_id') && shortcodeAttr[select.getAttribute('alt_id')] !== undefined) {
					template.querySelector('select[alt_id="' + select.getAttribute('alt_id') + '"]').value = shortcodeAttr[select.getAttribute('alt_id')];
				}
			});

			// Passing the template to the WindowManager has issues retrieving values, so we now dynamically get the fields
			// The previous step is necessary, otherwise the shortcode values are not passed.
			const fields = [];
			template.querySelectorAll('label').forEach(row => {
				const label = row.querySelector('span.label').innerText;
				const field = row.querySelector('input, select');
				const tooltip = row.querySelector('span.hint') ? row.querySelector('span.hint') : undefined;

				if (field) {
					const fieldObj = {
						type: field.nodeName === 'INPUT' ? 'textbox' : (field.nodeName === 'SELECT' ? 'listbox' : ''),
						name: field.name,
						label: label,
						value: field.value,
						tooltip: tooltip.innerText
					};
					if (field.nodeName === 'SELECT') {
						fieldObj.values = [];
						Array.from(field.children).forEach(option => {
							fieldObj.values.push({
								text: option.innerText,
								value: option.value
							});
						});
					}
					fields.push(fieldObj);
				}
			});

			editor.windowManager.open({
				title: 'Photonic Shortcode Editor - ' + (type === 'wp' ? 'WP' : type.substr(0,1).toUpperCase() + type.substr(1)),
				id: 'photonic-gallery-editor',
				width: 800,
				height: 400,
				body: fields,
				onsubmit: function(e) {
					let insertCode = '[' + Photonic_Admin_JS.shortcode + ' type="' + (type === 'wp' ? 'default' : type) + '"';
					const newCode = e.data;
					Object.entries(newCode).forEach(([idx, obj]) => {
						if (obj !== '') {
							insertCode += ' ' + idx + "='" + decodeURIComponent(obj).replace(/<[^>]+>/g, '') + "'";
						}
					});
					insertCode += ']';

					node.setAttribute('data-wpview-text', encodeURIComponent(insertCode));
				}
			});
		});

		// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
		editor.addCommand('Photonic_Gallery_Wizard', function(ui, v) {
			photonicClickedNode = editor.selection.getNode();

			// No s**t, but the TB code ignores everything after the TB_iframe=true parameter, hence appending TB_iframe=true to the end
			// tb_show('Edit Gallery', (Photonic_Admin_JS.flow_url + '&shortcode=' + scParameter).replace('&TB_iframe=true', '') + '&TB_iframe=true');
			tb_show('Edit Gallery', Photonic_Admin_JS.flow_url);
		});

		function verifyHTML(string) {
			const settings = {};

			if (!window.tinymce) {
				return string.replace(/<[^>]+>/g, '');
			}

			if (!string || (string.indexOf('<') === -1 && string.indexOf('>') === -1)) {
				return string;
			}

			const schema = new window.tinymce.html.Schema(settings);
			const parser = new window.tinymce.html.DomParser(settings, schema);
			const serializer = new window.tinymce.html.Serializer(settings, schema);

			return serializer.serialize(parser.parse(string, { forced_root_block: false }));
		}

		function getPhotonicType(img) {
			let type = 'default';
			if (img.classList.contains('photonic-gallery-flickr')) {
				type = 'flickr';
			}
			else if (img.classList.contains('photonic-gallery-google')) {
				type = 'google';
			}
			else if (img.classList.contains('photonic-gallery-smugmug')) {
				type = 'smugmug';
			}
			else if (img.classList.contains('photonic-gallery-zenfolio')) {
				type = 'zenfolio';
			}
			else if (img.classList.contains('photonic-gallery-instagram')) {
				type = 'instagram';
			}
			return type;
		}

		wp.mce.photonic_view_renderer = _.extend({}, wp.media.gallery, {
			shortcode_string: Photonic_Admin_JS.shortcode,
			state: [ 'gallery-edit' ],
			template: wp.media.template('editor-gallery'),

			// Lifted verbatim from mce-view.js, "base" code
			edit: function(text, update) {
				const media = wp.media;
				const type = this.type;
				if (type === Photonic_Admin_JS.shortcode && type !== 'gallery') {
					if (Photonic_Admin_JS.disable_flow) {
						editor.execCommand('Photonic_Gallery', '', {type: 'default'});
					}
					else {
						editor.execCommand('Photonic_Gallery_Wizard', '', {type: 'default'});
					}
					return;
				}

				let frame = media[ type ].edit(text);

				this.pausePlayers && this.pausePlayers();

				_.each(this.state, function(state) {
					frame.state(state).on('update', function(selection) {
						update(media[ type ].shortcode(selection).string(), type === 'gallery');
					});
				});

				frame.on('close', function() {
					frame.detach();
				});

				frame.open();
			},

			initialize: function() {
				const shortcodeAttr = this.shortcode.attrs.named;
				let type;
				if (shortcodeAttr['type'] === undefined) {
					type = Photonic_Admin_JS.default_gallery_type;
				}
				else {
					type = shortcodeAttr['type'];
				}
				if (type === 'default') {
					// Lifted, almost verbatim, from wp-includes/js/mce-view.js. This is the default gallery processing code.
					// If Photonic_Admin_JS.shortcode != 'gallery', the code from WP will be called anyway. Otherwise this code will be triggered
					// if type == 'default'.
					const media = wp.media;
					let attachments = media.gallery.attachments(this.shortcode, media.view.settings.post.id),
						attrs = this.shortcode.attrs.named,
						self = this;

					attachments.more()
						.done(function () {
							attachments = attachments.toJSON();

							_.each(attachments, function (attachment) {
								if (attachment.sizes) {
									if (attrs.size && attachment.sizes[attrs.size]) {
										attachment.thumbnail = attachment.sizes[attrs.size];
									}
									else if (attachment.sizes.thumbnail) {
										attachment.thumbnail = attachment.sizes.thumbnail;
									}
									else if (attachment.sizes.full) {
										attachment.thumbnail = attachment.sizes.full;
									}
								}
							});

							self.render(self.template({
								verifyHTML: verifyHTML,
								attachments: attachments,
								columns: attrs.columns ? parseInt(attrs.columns, 10) : media.galleryDefaults.columns
							}), attrs);
						})
						.fail(function (jqXHR, textStatus) {
								self.setError(textStatus);
							}
						);
				}
				else {
					this.content = html('wp-gallery photonic-gallery photonic-gallery-' + type, this.shortcode.string(), type === 'wp' ? 'WP' : type.substr(0, 1).toUpperCase() + type.substr(1));
				}
			}
		});

		editor.addButton('wp_view_edit', {
			tooltip: 'Edit ', // trailing space is needed, used for context
			icon: 'dashicon dashicons-edit',
			onclick: function() {
				const node = editor.selection.getNode();
				if (editor.dom.hasClass(node, 'wpview')) {
					const img = node.querySelector('img.photonic-gallery'); // Placeholder
					const div = node.querySelector('div.gallery');

					if ((img === null || !img.classList.contains('photonic-gallery')) && editor.dom.hasClass( node, 'wpview' )) { // Open Native Gallery editor if that is what is in "wpview".
						wp.mce.views.edit(editor, node);
					}
					else if ((div && Photonic_Admin_JS.shortcode !== 'gallery') || img.classList.contains('photonic-gallery')) {
						const type = getPhotonicType(img);
						if (Photonic_Admin_JS.disable_flow) {
							editor.execCommand('Photonic_Gallery', '', {type: type});
						}
						else {
							editor.execCommand('Photonic_Gallery_Wizard', '', {type: type});
						}
					}
				}
			}
		});

		editor.on('click keyup', function(e) {
			if (e.target.nodeName === 'IMG' && e.target.className.indexOf('photonic-gallery') > -1) {
				e.preventDefault();

				const type = getPhotonicType(e.target);
				if (Photonic_Admin_JS.disable_flow) {
					editor.execCommand('Photonic_Gallery', '', {type: type});
				}
				else {
					editor.execCommand('Photonic_Gallery_Wizard', '', {type: type});
				}
				return false;
			}
			else {
				return false;
			}
		});

		function sendDataToWizard(messageChannel) {
			const shortcode = wp.mce.views.getText(photonicClickedNode);
			const shortcodeObj = wp.shortcode.next(Photonic_Admin_JS.shortcode, shortcode);

			// Second, send the actual shortcode
			messageChannel.port1.postMessage({
				type: 'photonicMCENode',
				object: {
					shortcode: shortcodeObj
				}
			});
		}

		function getDataFromWizard(data) {
			if (photonicClickedNode) {
				photonicClickedNode.setAttribute('data-wpview-text', encodeURIComponent(data.html));
			}
			else {
				editor.execCommand('mceInsertContent', false, data.html);
			}
		}

		if (wizardButton) {
			wizardButton.addEventListener('click', e => {
				// Need to null this out, since a user can click on the button to add a new gallery, but the `photonicClickedNode` might be carrying the previously clicked node's data
				photonicClickedNode = null;
			});
		}

		if (mceTab && htmlTab) {
			[mceTab, htmlTab].forEach(button => {
				button.addEventListener('click', e => {
					if (lastClickedTab.id !== button.id) {
						lastClickedTab = button;
						if (button.id === mceTab.id) {
							nativeMediaLibrary.waitForIFrame();
						}
					}
				});
			});
		}

		wp.mce.views.register(Photonic_Admin_JS.shortcode, wp.mce.photonic_view_renderer);
	});
})(jQuery);
