/**
 * block.js - Contains all Gutenberg functionality required by Photonic
 */
let photonicBlockProperties; // Must use this, since the properties of a block are getting reset in the "waitForIFrame" call
(function (wp) {
	const el = wp.element.createElement;
	const __ = wp.i18n.__;
	let components = wp.components;
	const iconEl = el('svg', {width: 23, height: 24, viewBox: "0 0 24 24"},
		el('g', {transform: "scale(0.046785)"},
			el('circle', {cx: "256", cy: "192", r: "128", fill: "#0085ba"}),
			el('rect', {width: "64", height: "256", x: "128", y: "192", fill: "#0085ba"}),
			el('circle', {cx: "256", cy: "192", r: "64", fill: "white"}),
			el('rect', {width: "16", height: "128", x: "192", y: "192", fill: "white"})
		)
	);

	const tag = Photonic_Gutenberg_JS.shortcode.toLowerCase() === 'gallery' ? 'gallery__photonic_random_314159' : Photonic_Gutenberg_JS.shortcode;
	wp.blocks.registerBlockType('photonic/gallery', {
		title: __('Photonic Gallery', 'photonic'),
		apiVersion: 3,
		category: 'widgets',
		keywords: ['flickr', 'smugmug', 'google'],
		icon: iconEl,
		supports: {
			html: false,
			align: ['wide', 'full']
		},

		transforms: {
			from: [
				{
					type: 'shortcode',
					tag: tag,
					attributes: {
						shortcode: {
							type: 'string',
							shortcode: function (named) {
								return JSON.stringify(named.named);
							}
						}
					}
				},
				// Works for WP >= 5.4, which is the current release. Will not work for older versions, so this will be uncommented
				// once WP 5.6 is out. Photonic is compatible with WP upto 2 versions old, and WP 4.9.
				{
					type: 'shortcode',
					tag: 'gallery',
					isMatch: function (attr) {
						const layouts = ['square', 'circle', 'random', 'mosaic', 'masonry', 'strip-above', 'strip-below', 'strip-right', 'no-strip'];
						const providers = ['flickr', 'smugmug', 'google', 'zenfolio', 'instagram'];
						return (attr.named.style !== undefined && layouts.indexOf(attr.named.style) >= 0 && attr.named.type === undefined) ||
							(attr.named.type !== undefined && providers.indexOf(attr.named.type) >= 0 && attr.named.layout !== undefined && layouts.indexOf(attr.named.layout) >= 0);
					},
					attributes: {
						shortcode: {
							type: 'string',
							shortcode: function (named) {
								return JSON.stringify(named.named);
							}
						}
					}
				},
				{
					type: 'block',
					blocks: ['core/gallery'],
					transform: function (attributes) {
						const images = attributes.images;
						let ids = '';
						Array.prototype.forEach.call(images, function (image) {
							ids += image.id + ',';
						});

						if (ids.length > 0) {
							ids = ids.slice(0, -1);
						}

						let sc = {
							type: 'wp',
							ids: ids
						};

						return wp.blocks.createBlock('photonic/gallery', {
							shortcode: JSON.stringify(sc)
						});
					}
				}
			]
		},

		attributes: {
			shortcode: {
				type: 'string'
			}
		},

		/**
		 * Called when Gutenberg initially loads the block.
		 */
		edit: function (props) {
			let sourceMessageChannel;
			let nativeMediaLibrary = new PhotonicWPNativeUI(sendDataToWizard, getDataFromWizard);
			let markup = [], iconClass = '';
			let shortcode = props.attributes.shortcode || '{}';
			shortcode = JSON.parse(shortcode);

			const blockProps = wp.blockEditor.useBlockProps(); // Required for the "container" for the block, since apiVersion 2 & higher

			const providers = {
				'wp': 'WordPress',
				'flickr': 'Flickr',
				'smugmug': 'SmugMug',
				'google': 'Google Photos',
				'picasa': 'Picasa',
				'zenfolio': 'Zenfolio',
				'instagram': 'Instagram'
			};
			let source;

			if (JSON.stringify(shortcode) !== JSON.stringify({}) && (shortcode.type === undefined || shortcode.type === 'default')) {
				iconClass = 'photonic-wp';
				source = 'wp';
			}
			else if (shortcode.type !== undefined && ['wp', 'flickr', 'smugmug', 'google', 'picasa', 'zenfolio', 'instagram'].indexOf(shortcode.type) > -1) {
				iconClass = 'photonic-' + shortcode.type;
				source = shortcode.type;
			}

			const title = iconClass === '' ? __('Add Photonic Gallery', 'photonic') : __('Edit Photonic Gallery', 'photonic') + ' (' + __('Source: ', 'photonic') + providers[source] + ')';

			nativeMediaLibrary.waitForIFrame();

			const openWizard = () => {
				photonicBlockProperties = props; // Must use this, since the properties of a block are getting reset in the "waitForIFrame" call
				tb_show(title, Photonic_Gutenberg_JS.flow_url);
			};

			function sendDataToWizard(messageChannel) {
				messageChannel.port1.postMessage({
					type: 'photonicBlock',
					object: photonicBlockProperties.attributes, // Must use this, since the properties of a block are getting reset in the "waitForIFrame" call
				});
			}

			function getDataFromWizard(data) {
				photonicBlockProperties.setAttributes({shortcode: data.props});
			}

			markup.push(
				el('div', blockProps,
					el('div', {key: 'photonic-placeholder', className: 'photonic-gallery'},
						el('a', {className: 'photonic-placeholder-icon photonic ' + iconClass, onClick: openWizard}),
						title
					)
				)
			);

			return (markup);
		},

		/**
		 * Called when Gutenberg "saves" the block to post_content
		 */
		save: function (props) {
			return null;
		}
	});
})(window.wp);
