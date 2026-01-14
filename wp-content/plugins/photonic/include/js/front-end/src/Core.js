import * as Util from "./Util";
import {Tooltip} from "./Components/Tooltip";
import {Prompter} from "./Components/Prompter";

export class Core {
	static lightboxList = [];
	static prompterList = [];

	static lightbox;
	static deep = location.hash;

	static setLightbox = (lb) => this.lightbox = lb;
	static getLightbox = () => this.lightbox;

	static setDeep = (d) => this.deep = d;
	static getDeep = () => this.deep;

	static addToLightboxList = (idx, lightbox) => this.lightboxList[idx] = lightbox;
	static getLightboxList = () => this.lightboxList;

	static showSpinner = () => {
		let loading = document.getElementsByClassName('photonic-loading');
		if (loading.length > 0) {
			loading = loading[0];
		}
		else {
			loading = document.createElement('div');
			loading.className = 'photonic-loading';
		}
		loading.style.display = 'block';
		document.body.appendChild(loading);
	};

	static hideLoading = () => {
		let loading = document.getElementsByClassName('photonic-loading');
		if (loading.length > 0) {
			loading = loading[0];
			loading.style.display = 'none';
		}
	};

	static initializePasswordPrompter = selector => {
		const selectorNoHash = selector.replace(/^#+/g, '');
		const prompter = new Prompter(selectorNoHash);
		prompter.attach();
		this.prompterList[selector] = prompter;
		prompter.show();
	};

	static moveHTML5External = () => {
		let videos = document.getElementById('photonic-html5-external-videos');
		if (!videos) {
			videos = document.createElement('div');
			videos.id = 'photonic-html5-external-videos';
			videos.style.display = 'none';
			document.body.appendChild(videos);
		}

		const current = document.querySelectorAll('.photonic-html5-external');
		if (current) {
			const cLen = current.length;
			for (let c = 0; c < cLen; c++) {
				current[c].classList.remove('photonic-html5-external');
				videos.appendChild(current[c]);
			}
		}
	};

	static blankSlideupTitle = () => {
		document.querySelectorAll('.title-display-slideup-stick, .photonic-slideshow.title-display-slideup-stick').forEach((item) => {
			Array.from(item.getElementsByTagName('a')).forEach(a => {
				a.setAttribute('title', '');
			});
		});
	};

	static showSlideupTitle = () => {
		let titles = document.documentElement.querySelectorAll('.title-display-slideup-stick a .photonic-title');
		const len = titles.length;
		for (let i = 0; i < len; i++) {
			titles[i].style.display = 'block';
		}
	};

	static waitForImages = async (selector) => {
		let imageUrlArray = [];
		let anchorArray = [];
		let setDimensions = false;

		if (typeof selector === 'string') {
			document.querySelectorAll(selector).forEach(selection => {
				Array.from(selection.getElementsByTagName('img')).forEach(img => {
					imageUrlArray.push(img.getAttribute('src'));
					anchorArray.push(img.parentElement);
				});
			});
		}
		else if (selector instanceof Element) {
			if (selector.getAttribute('data-photonic-platform') === 'instagram') {
				setDimensions = true;
			}
			Array.from(selector.getElementsByTagName('img')).forEach(img => {
				imageUrlArray.push(img.getAttribute('src'));
				anchorArray.push(img.parentElement);
			});
		}

		const promiseArray = []; // create an array for promises
		const imageArray = []; // array for the images

		for (const [idx, imageUrl] of imageUrlArray.entries()) {
			promiseArray.push(new Promise(resolve => {
				const img = new Image();
				img.onload = () => {
					if (setDimensions) {
						anchorArray[idx].setAttribute('data-pswp-width', img.naturalWidth);
						anchorArray[idx].setAttribute('data-pswp-height', img.naturalHeight);
					}
					resolve();
				};

				img.src = imageUrl;
				imageArray.push(img);
			}));
		}

		await Promise.all(promiseArray); // wait for all the images to be loaded
		return imageArray;
	};

	static standardizeTitleWidths = () => {
		const self = this;
		document.querySelectorAll('.photonic-standard-layout.title-display-below, .photonic-standard-layout.title-display-hover-slideup-show, .photonic-standard-layout.title-display-slideup-stick').forEach(grid => {
			self.waitForImages(grid).then(() => {
				grid.querySelectorAll('.photonic-thumb').forEach(item => {
					let img = item.getElementsByTagName('img');
					if (img != null) {
						img = img[0];
						let title = item.querySelector('.photonic-title-info');
						if (title) {
							title.style.width = img.width + 'px';
						}
					}
				});
			});
		});
	};

	static sanitizeTitles = () => {
		const thumbs = document.querySelectorAll('.photonic-stream a, a.photonic-level-2-thumb');
		thumbs.forEach((thumb) => {
			if (!thumb.parentNode.classList.contains('photonic-header-title')) {
				const title = thumb.getAttribute('title');
				thumb.setAttribute('title', Util.getText(title));
			}
		});
	};

	static initializeTooltips = () => {
		if (document.querySelector('.title-display-tooltip a, .photonic-slideshow.title-display-tooltip img') != null) {
			Tooltip('[data-photonic-tooltip]', '.photonic-tooltip-container');
		}
	};

	static showRegularGrids = () => {
		document.querySelectorAll('.photonic-standard-layout').forEach(grid => {
			this.waitForImages(grid).then(() => {
				grid.querySelectorAll('.photonic-level-1, .photonic-level-2').forEach(item => {
					item.style.display = 'inline-block';
				});
			});
		});
	};

	static executeCommon = () => {
		Core.moveHTML5External();
		Core.blankSlideupTitle();
		Core.standardizeTitleWidths();
		Core.sanitizeTitles();
		Core.initializeTooltips();
		Core.showRegularGrids();
	};
}
