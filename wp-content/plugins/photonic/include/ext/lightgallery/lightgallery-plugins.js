/*!
 * lightgallery | 2.4.0 | January 29th 2022
 * http://www.lightgalleryjs.com/
 * Copyright (c) 2020 Sachin Neravath;
 * @license GPLv3
 */

(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
		typeof define === 'function' && define.amd ? define(factory) :
			(global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.lgAutoplay = factory());
}(this, (function () { 'use strict';

	/*! *****************************************************************************
	Copyright (c) Microsoft Corporation.

	Permission to use, copy, modify, and/or distribute this software for any
	purpose with or without fee is hereby granted.

	THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
	REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
	INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
	LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
	OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
	PERFORMANCE OF THIS SOFTWARE.
	***************************************************************************** */

	var __assign = function() {
		__assign = Object.assign || function __assign(t) {
			for (var s, i = 1, n = arguments.length; i < n; i++) {
				s = arguments[i];
				for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
			}
			return t;
		};
		return __assign.apply(this, arguments);
	};

	/**
	 * List of lightGallery events
	 * All events should be documented here
	 * Below interfaces are used to build the website documentations
	 * */
	var lGEvents = {
		afterAppendSlide: 'lgAfterAppendSlide',
		init: 'lgInit',
		hasVideo: 'lgHasVideo',
		containerResize: 'lgContainerResize',
		updateSlides: 'lgUpdateSlides',
		afterAppendSubHtml: 'lgAfterAppendSubHtml',
		beforeOpen: 'lgBeforeOpen',
		afterOpen: 'lgAfterOpen',
		slideItemLoad: 'lgSlideItemLoad',
		beforeSlide: 'lgBeforeSlide',
		afterSlide: 'lgAfterSlide',
		posterClick: 'lgPosterClick',
		dragStart: 'lgDragStart',
		dragMove: 'lgDragMove',
		dragEnd: 'lgDragEnd',
		beforeNextSlide: 'lgBeforeNextSlide',
		beforePrevSlide: 'lgBeforePrevSlide',
		beforeClose: 'lgBeforeClose',
		afterClose: 'lgAfterClose',
		rotateLeft: 'lgRotateLeft',
		rotateRight: 'lgRotateRight',
		flipHorizontal: 'lgFlipHorizontal',
		flipVertical: 'lgFlipVertical',
		autoplay: 'lgAutoplay',
		autoplayStart: 'lgAutoplayStart',
		autoplayStop: 'lgAutoplayStop',
	};

	var autoplaySettings = {
		autoplay: true,
		slideShowAutoplay: false,
		slideShowInterval: 5000,
		progressBar: true,
		forceSlideShowAutoplay: false,
		autoplayControls: true,
		appendAutoplayControlsTo: '.lg-toolbar',
		autoplayPluginStrings: {
			toggleAutoplay: 'Toggle Autoplay',
		},
	};

	/**
	 * Creates the autoplay plugin.
	 * @param {object} element - lightGallery element
	 */
	var Autoplay = /** @class */ (function () {
		function Autoplay(instance) {
			this.core = instance;
			// extend module default settings with lightGallery core settings
			this.settings = __assign(__assign({}, autoplaySettings), this.core.settings);
			return this;
		}
		Autoplay.prototype.init = function () {
			var _this = this;
			if (!this.settings.autoplay) {
				return;
			}
			this.interval = false;
			// Identify if slide happened from autoplay
			this.fromAuto = true;
			// Identify if autoplay canceled from touch/drag
			this.pausedOnTouchDrag = false;
			this.pausedOnSlideChange = false;
			// append autoplay controls
			if (this.settings.autoplayControls) {
				this.controls();
			}
			// Create progress bar
			if (this.settings.progressBar) {
				this.core.outer.append('<div class="lg-progress-bar"><div class="lg-progress"></div></div>');
			}
			// Start autoplay
			if (this.settings.slideShowAutoplay) {
				this.core.LGel.once(lGEvents.slideItemLoad + ".autoplay", function () {
					_this.startAutoPlay();
				});
			}
			// cancel interval on touchstart and dragstart
			this.core.LGel.on(lGEvents.dragStart + ".autoplay touchstart.lg.autoplay", function () {
				if (_this.interval) {
					_this.stopAutoPlay();
					_this.pausedOnTouchDrag = true;
				}
			});
			// restore autoplay if autoplay canceled from touchstart / dragstart
			this.core.LGel.on(lGEvents.dragEnd + ".autoplay touchend.lg.autoplay", function () {
				if (!_this.interval && _this.pausedOnTouchDrag) {
					_this.startAutoPlay();
					_this.pausedOnTouchDrag = false;
				}
			});
			this.core.LGel.on(lGEvents.beforeSlide + ".autoplay", function () {
				_this.showProgressBar();
				if (!_this.fromAuto && _this.interval) {
					_this.stopAutoPlay();
					_this.pausedOnSlideChange = true;
				}
				else {
					_this.pausedOnSlideChange = false;
				}
				_this.fromAuto = false;
			});
			// restore autoplay if autoplay canceled from touchstart / dragstart
			this.core.LGel.on(lGEvents.afterSlide + ".autoplay", function () {
				if (_this.pausedOnSlideChange &&
					!_this.interval &&
					_this.settings.forceSlideShowAutoplay) {
					_this.startAutoPlay();
					_this.pausedOnSlideChange = false;
				}
			});
			// set progress
			this.showProgressBar();
		};
		Autoplay.prototype.showProgressBar = function () {
			var _this = this;
			if (this.settings.progressBar && this.fromAuto) {
				var _$progressBar_1 = this.core.outer.find('.lg-progress-bar');
				var _$progress_1 = this.core.outer.find('.lg-progress');
				if (this.interval) {
					_$progress_1.removeAttr('style');
					_$progressBar_1.removeClass('lg-start');
					setTimeout(function () {
						_$progress_1.css('transition', 'width ' +
							(_this.core.settings.speed +
								_this.settings.slideShowInterval) +
							'ms ease 0s');
						_$progressBar_1.addClass('lg-start');
					}, 20);
				}
			}
		};
		// Manage autoplay via play/stop buttons
		Autoplay.prototype.controls = function () {
			var _this = this;
			var _html = "<button aria-label=\"" + this.settings.autoplayPluginStrings['toggleAutoplay'] + "\" type=\"button\" class=\"lg-autoplay-button lg-icon\"></button>";
			// Append autoplay controls
			this.core.outer
				.find(this.settings.appendAutoplayControlsTo)
				.append(_html);
			this.core.outer
				.find('.lg-autoplay-button')
				.first()
				.on('click.lg.autoplay', function () {
					if (_this.core.outer.hasClass('lg-show-autoplay')) {
						_this.stopAutoPlay();
					}
					else {
						if (!_this.interval) {
							_this.startAutoPlay();
						}
					}
				});
		};
		// Autostart gallery
		Autoplay.prototype.startAutoPlay = function () {
			var _this = this;
			this.core.outer
				.find('.lg-progress')
				.css('transition', 'width ' +
					(this.core.settings.speed +
						this.settings.slideShowInterval) +
					'ms ease 0s');
			this.core.outer.addClass('lg-show-autoplay');
			this.core.outer.find('.lg-progress-bar').addClass('lg-start');
			this.core.LGel.trigger(lGEvents.autoplayStart, {
				index: this.core.index,
			});
			this.interval = setInterval(function () {
				if (_this.core.index + 1 < _this.core.galleryItems.length) {
					_this.core.index++;
				}
				else {
					_this.core.index = 0;
				}
				_this.core.LGel.trigger(lGEvents.autoplay, {
					index: _this.core.index,
				});
				_this.fromAuto = true;
				_this.core.slide(_this.core.index, false, false, 'next');
			}, this.core.settings.speed + this.settings.slideShowInterval);
		};
		// cancel Autostart
		Autoplay.prototype.stopAutoPlay = function () {
			if (this.interval) {
				this.core.LGel.trigger(lGEvents.autoplayStop, {
					index: this.core.index,
				});
				this.core.outer.find('.lg-progress').removeAttr('style');
				this.core.outer.removeClass('lg-show-autoplay');
				this.core.outer.find('.lg-progress-bar').removeClass('lg-start');
			}
			clearInterval(this.interval);
			this.interval = false;
		};
		Autoplay.prototype.closeGallery = function () {
			this.stopAutoPlay();
		};
		Autoplay.prototype.destroy = function () {
			if (this.settings.autoplay) {
				this.core.outer.find('.lg-progress-bar').remove();
			}
			// Remove all event listeners added by autoplay plugin
			this.core.LGel.off('.lg.autoplay');
			this.core.LGel.off('.autoplay');
		};
		return Autoplay;
	}());

	return Autoplay;

})));

/*!
 * lightgallery | 2.4.0 | January 29th 2022
 * http://www.lightgalleryjs.com/
 * Copyright (c) 2020 Sachin Neravath;
 * @license GPLv3
 */

(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
		typeof define === 'function' && define.amd ? define(factory) :
			(global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.lgFullscreen = factory());
}(this, (function () { 'use strict';

	/*! *****************************************************************************
	Copyright (c) Microsoft Corporation.

	Permission to use, copy, modify, and/or distribute this software for any
	purpose with or without fee is hereby granted.

	THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
	REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
	INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
	LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
	OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
	PERFORMANCE OF THIS SOFTWARE.
	***************************************************************************** */

	var __assign = function() {
		__assign = Object.assign || function __assign(t) {
			for (var s, i = 1, n = arguments.length; i < n; i++) {
				s = arguments[i];
				for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
			}
			return t;
		};
		return __assign.apply(this, arguments);
	};

	var fullscreenSettings = {
		fullScreen: true,
		fullscreenPluginStrings: {
			toggleFullscreen: 'Toggle Fullscreen',
		},
	};

	var FullScreen = /** @class */ (function () {
		function FullScreen(instance, $LG) {
			// get lightGallery core plugin instance
			this.core = instance;
			this.$LG = $LG;
			// extend module default settings with lightGallery core settings
			this.settings = __assign(__assign({}, fullscreenSettings), this.core.settings);
			return this;
		}
		FullScreen.prototype.init = function () {
			var fullScreen = '';
			if (this.settings.fullScreen) {
				// check for fullscreen browser support
				if (!document.fullscreenEnabled &&
					!document.webkitFullscreenEnabled &&
					!document.mozFullScreenEnabled &&
					!document.msFullscreenEnabled) {
					return;
				}
				else {
					fullScreen = "<button type=\"button\" aria-label=\"" + this.settings.fullscreenPluginStrings['toggleFullscreen'] + "\" class=\"lg-fullscreen lg-icon\"></button>";
					this.core.$toolbar.append(fullScreen);
					this.fullScreen();
				}
			}
		};
		FullScreen.prototype.isFullScreen = function () {
			return (document.fullscreenElement ||
				document.mozFullScreenElement ||
				document.webkitFullscreenElement ||
				document.msFullscreenElement);
		};
		FullScreen.prototype.requestFullscreen = function () {
			var el = document.documentElement;
			if (el.requestFullscreen) {
				el.requestFullscreen();
			}
			else if (el.msRequestFullscreen) {
				el.msRequestFullscreen();
			}
			else if (el.mozRequestFullScreen) {
				el.mozRequestFullScreen();
			}
			else if (el.webkitRequestFullscreen) {
				el.webkitRequestFullscreen();
			}
		};
		FullScreen.prototype.exitFullscreen = function () {
			if (document.exitFullscreen) {
				document.exitFullscreen();
			}
			else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
			else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			}
			else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			}
		};
		// https://developer.mozilla.org/en-US/docs/Web/Guide/API/DOM/Using_full_screen_mode
		FullScreen.prototype.fullScreen = function () {
			var _this = this;
			this.$LG(document).on("fullscreenchange.lg.global" + this.core.lgId + " \n            webkitfullscreenchange.lg.global" + this.core.lgId + " \n            mozfullscreenchange.lg.global" + this.core.lgId + " \n            MSFullscreenChange.lg.global" + this.core.lgId, function () {
				if (!_this.core.lgOpened)
					return;
				_this.core.outer.toggleClass('lg-fullscreen-on');
			});
			this.core.outer
				.find('.lg-fullscreen')
				.first()
				.on('click.lg', function () {
					if (_this.isFullScreen()) {
						_this.exitFullscreen();
					}
					else {
						_this.requestFullscreen();
					}
				});
		};
		FullScreen.prototype.closeGallery = function () {
			// exit from fullscreen if activated
			if (this.isFullScreen()) {
				this.exitFullscreen();
			}
		};
		FullScreen.prototype.destroy = function () {
			this.$LG(document).off("fullscreenchange.lg.global" + this.core.lgId + " \n            webkitfullscreenchange.lg.global" + this.core.lgId + " \n            mozfullscreenchange.lg.global" + this.core.lgId + " \n            MSFullscreenChange.lg.global" + this.core.lgId);
		};
		return FullScreen;
	}());

	return FullScreen;

})));

/*!
 * lightgallery | 2.4.0 | January 29th 2022
 * http://www.lightgalleryjs.com/
 * Copyright (c) 2020 Sachin Neravath;
 * @license GPLv3
 */

(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
		typeof define === 'function' && define.amd ? define(factory) :
			(global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.lgThumbnail = factory());
}(this, (function () { 'use strict';

	/*! *****************************************************************************
	Copyright (c) Microsoft Corporation.

	Permission to use, copy, modify, and/or distribute this software for any
	purpose with or without fee is hereby granted.

	THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
	REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
	INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
	LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
	OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
	PERFORMANCE OF THIS SOFTWARE.
	***************************************************************************** */

	var __assign = function() {
		__assign = Object.assign || function __assign(t) {
			for (var s, i = 1, n = arguments.length; i < n; i++) {
				s = arguments[i];
				for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
			}
			return t;
		};
		return __assign.apply(this, arguments);
	};

	var thumbnailsSettings = {
		thumbnail: true,
		animateThumb: true,
		currentPagerPosition: 'middle',
		alignThumbnails: 'middle',
		thumbWidth: 100,
		thumbHeight: '80px',
		thumbMargin: 5,
		appendThumbnailsTo: '.lg-components',
		toggleThumb: false,
		enableThumbDrag: true,
		enableThumbSwipe: true,
		thumbnailSwipeThreshold: 10,
		loadYouTubeThumbnail: true,
		youTubeThumbSize: 1,
		thumbnailPluginStrings: {
			toggleThumbnails: 'Toggle thumbnails',
		},
	};

	/**
	 * List of lightGallery events
	 * All events should be documented here
	 * Below interfaces are used to build the website documentations
	 * */
	var lGEvents = {
		afterAppendSlide: 'lgAfterAppendSlide',
		init: 'lgInit',
		hasVideo: 'lgHasVideo',
		containerResize: 'lgContainerResize',
		updateSlides: 'lgUpdateSlides',
		afterAppendSubHtml: 'lgAfterAppendSubHtml',
		beforeOpen: 'lgBeforeOpen',
		afterOpen: 'lgAfterOpen',
		slideItemLoad: 'lgSlideItemLoad',
		beforeSlide: 'lgBeforeSlide',
		afterSlide: 'lgAfterSlide',
		posterClick: 'lgPosterClick',
		dragStart: 'lgDragStart',
		dragMove: 'lgDragMove',
		dragEnd: 'lgDragEnd',
		beforeNextSlide: 'lgBeforeNextSlide',
		beforePrevSlide: 'lgBeforePrevSlide',
		beforeClose: 'lgBeforeClose',
		afterClose: 'lgAfterClose',
		rotateLeft: 'lgRotateLeft',
		rotateRight: 'lgRotateRight',
		flipHorizontal: 'lgFlipHorizontal',
		flipVertical: 'lgFlipVertical',
		autoplay: 'lgAutoplay',
		autoplayStart: 'lgAutoplayStart',
		autoplayStop: 'lgAutoplayStop',
	};

	var Thumbnail = /** @class */ (function () {
		function Thumbnail(instance, $LG) {
			this.thumbOuterWidth = 0;
			this.thumbTotalWidth = 0;
			this.translateX = 0;
			this.thumbClickable = false;
			// get lightGallery core plugin instance
			this.core = instance;
			this.$LG = $LG;
			return this;
		}
		Thumbnail.prototype.init = function () {
			// extend module default settings with lightGallery core settings
			this.settings = __assign(__assign({}, thumbnailsSettings), this.core.settings);
			this.thumbOuterWidth = 0;
			this.thumbTotalWidth =
				this.core.galleryItems.length *
				(this.settings.thumbWidth + this.settings.thumbMargin);
			// Thumbnail animation value
			this.translateX = 0;
			this.setAnimateThumbStyles();
			if (!this.core.settings.allowMediaOverlap) {
				this.settings.toggleThumb = false;
			}
			if (this.settings.thumbnail) {
				this.build();
				if (this.settings.animateThumb) {
					if (this.settings.enableThumbDrag) {
						this.enableThumbDrag();
					}
					if (this.settings.enableThumbSwipe) {
						this.enableThumbSwipe();
					}
					this.thumbClickable = false;
				}
				else {
					this.thumbClickable = true;
				}
				this.toggleThumbBar();
				this.thumbKeyPress();
			}
		};
		Thumbnail.prototype.build = function () {
			var _this = this;
			this.setThumbMarkup();
			this.manageActiveClassOnSlideChange();
			this.$lgThumb.first().on('click.lg touchend.lg', function (e) {
				var $target = _this.$LG(e.target);
				if (!$target.hasAttribute('data-lg-item-id')) {
					return;
				}
				setTimeout(function () {
					// In IE9 and bellow touch does not support
					// Go to slide if browser does not support css transitions
					if (_this.thumbClickable && !_this.core.lgBusy) {
						var index = parseInt($target.attr('data-lg-item-id'));
						_this.core.slide(index, false, true, false);
					}
				}, 50);
			});
			this.core.LGel.on(lGEvents.beforeSlide + ".thumb", function (event) {
				var index = event.detail.index;
				_this.animateThumb(index);
			});
			this.core.LGel.on(lGEvents.beforeOpen + ".thumb", function () {
				_this.thumbOuterWidth = _this.core.outer.get().offsetWidth;
			});
			this.core.LGel.on(lGEvents.updateSlides + ".thumb", function () {
				_this.rebuildThumbnails();
			});
			this.core.LGel.on(lGEvents.containerResize + ".thumb", function () {
				if (!_this.core.lgOpened)
					return;
				setTimeout(function () {
					_this.thumbOuterWidth = _this.core.outer.get().offsetWidth;
					_this.animateThumb(_this.core.index);
					_this.thumbOuterWidth = _this.core.outer.get().offsetWidth;
				}, 50);
			});
		};
		Thumbnail.prototype.setThumbMarkup = function () {
			var thumbOuterClassNames = 'lg-thumb-outer ';
			if (this.settings.alignThumbnails) {
				thumbOuterClassNames += "lg-thumb-align-" + this.settings.alignThumbnails;
			}
			var html = "<div class=\"" + thumbOuterClassNames + "\">\n        <div class=\"lg-thumb lg-group\">\n        </div>\n        </div>";
			this.core.outer.addClass('lg-has-thumb');
			if (this.settings.appendThumbnailsTo === '.lg-components') {
				this.core.$lgComponents.append(html);
			}
			else {
				this.core.outer.append(html);
			}
			this.$thumbOuter = this.core.outer.find('.lg-thumb-outer').first();
			this.$lgThumb = this.core.outer.find('.lg-thumb').first();
			if (this.settings.animateThumb) {
				this.core.outer
					.find('.lg-thumb')
					.css('transition-duration', this.core.settings.speed + 'ms')
					.css('width', this.thumbTotalWidth + 'px')
					.css('position', 'relative');
			}
			this.setThumbItemHtml(this.core.galleryItems);
		};
		Thumbnail.prototype.enableThumbDrag = function () {
			var _this = this;
			var thumbDragUtils = {
				cords: {
					startX: 0,
					endX: 0,
				},
				isMoved: false,
				newTranslateX: 0,
				startTime: new Date(),
				endTime: new Date(),
				touchMoveTime: 0,
			};
			var isDragging = false;
			this.$thumbOuter.addClass('lg-grab');
			this.core.outer
				.find('.lg-thumb')
				.first()
				.on('mousedown.lg.thumb', function (e) {
					if (_this.thumbTotalWidth > _this.thumbOuterWidth) {
						// execute only on .lg-object
						e.preventDefault();
						thumbDragUtils.cords.startX = e.pageX;
						thumbDragUtils.startTime = new Date();
						_this.thumbClickable = false;
						isDragging = true;
						// ** Fix for webkit cursor issue https://code.google.com/p/chromium/issues/detail?id=26723
						_this.core.outer.get().scrollLeft += 1;
						_this.core.outer.get().scrollLeft -= 1;
						// *
						_this.$thumbOuter
							.removeClass('lg-grab')
							.addClass('lg-grabbing');
					}
				});
			this.$LG(window).on("mousemove.lg.thumb.global" + this.core.lgId, function (e) {
				if (!_this.core.lgOpened)
					return;
				if (isDragging) {
					thumbDragUtils.cords.endX = e.pageX;
					thumbDragUtils = _this.onThumbTouchMove(thumbDragUtils);
				}
			});
			this.$LG(window).on("mouseup.lg.thumb.global" + this.core.lgId, function () {
				if (!_this.core.lgOpened)
					return;
				if (thumbDragUtils.isMoved) {
					thumbDragUtils = _this.onThumbTouchEnd(thumbDragUtils);
				}
				else {
					_this.thumbClickable = true;
				}
				if (isDragging) {
					isDragging = false;
					_this.$thumbOuter.removeClass('lg-grabbing').addClass('lg-grab');
				}
			});
		};
		Thumbnail.prototype.enableThumbSwipe = function () {
			var _this = this;
			var thumbDragUtils = {
				cords: {
					startX: 0,
					endX: 0,
				},
				isMoved: false,
				newTranslateX: 0,
				startTime: new Date(),
				endTime: new Date(),
				touchMoveTime: 0,
			};
			this.$lgThumb.on('touchstart.lg', function (e) {
				if (_this.thumbTotalWidth > _this.thumbOuterWidth) {
					e.preventDefault();
					thumbDragUtils.cords.startX = e.targetTouches[0].pageX;
					_this.thumbClickable = false;
					thumbDragUtils.startTime = new Date();
				}
			});
			this.$lgThumb.on('touchmove.lg', function (e) {
				if (_this.thumbTotalWidth > _this.thumbOuterWidth) {
					e.preventDefault();
					thumbDragUtils.cords.endX = e.targetTouches[0].pageX;
					thumbDragUtils = _this.onThumbTouchMove(thumbDragUtils);
				}
			});
			this.$lgThumb.on('touchend.lg', function () {
				if (thumbDragUtils.isMoved) {
					thumbDragUtils = _this.onThumbTouchEnd(thumbDragUtils);
				}
				else {
					_this.thumbClickable = true;
				}
			});
		};
		// Rebuild thumbnails
		Thumbnail.prototype.rebuildThumbnails = function () {
			var _this = this;
			// Remove transitions
			this.$thumbOuter.addClass('lg-rebuilding-thumbnails');
			setTimeout(function () {
				_this.thumbTotalWidth =
					_this.core.galleryItems.length *
					(_this.settings.thumbWidth + _this.settings.thumbMargin);
				_this.$lgThumb.css('width', _this.thumbTotalWidth + 'px');
				_this.$lgThumb.empty();
				_this.setThumbItemHtml(_this.core.galleryItems);
				_this.animateThumb(_this.core.index);
			}, 50);
			setTimeout(function () {
				_this.$thumbOuter.removeClass('lg-rebuilding-thumbnails');
			}, 200);
		};
		// @ts-check
		Thumbnail.prototype.setTranslate = function (value) {
			this.$lgThumb.css('transform', 'translate3d(-' + value + 'px, 0px, 0px)');
		};
		Thumbnail.prototype.getPossibleTransformX = function (left) {
			if (left > this.thumbTotalWidth - this.thumbOuterWidth) {
				left = this.thumbTotalWidth - this.thumbOuterWidth;
			}
			if (left < 0) {
				left = 0;
			}
			return left;
		};
		Thumbnail.prototype.animateThumb = function (index) {
			this.$lgThumb.css('transition-duration', this.core.settings.speed + 'ms');
			if (this.settings.animateThumb) {
				var position = 0;
				switch (this.settings.currentPagerPosition) {
					case 'left':
						position = 0;
						break;
					case 'middle':
						position =
							this.thumbOuterWidth / 2 - this.settings.thumbWidth / 2;
						break;
					case 'right':
						position = this.thumbOuterWidth - this.settings.thumbWidth;
				}
				this.translateX =
					(this.settings.thumbWidth + this.settings.thumbMargin) * index -
					1 -
					position;
				if (this.translateX > this.thumbTotalWidth - this.thumbOuterWidth) {
					this.translateX = this.thumbTotalWidth - this.thumbOuterWidth;
				}
				if (this.translateX < 0) {
					this.translateX = 0;
				}
				this.setTranslate(this.translateX);
			}
		};
		Thumbnail.prototype.onThumbTouchMove = function (thumbDragUtils) {
			thumbDragUtils.newTranslateX = this.translateX;
			thumbDragUtils.isMoved = true;
			thumbDragUtils.touchMoveTime = new Date().valueOf();
			thumbDragUtils.newTranslateX -=
				thumbDragUtils.cords.endX - thumbDragUtils.cords.startX;
			thumbDragUtils.newTranslateX = this.getPossibleTransformX(thumbDragUtils.newTranslateX);
			// move current slide
			this.setTranslate(thumbDragUtils.newTranslateX);
			this.$thumbOuter.addClass('lg-dragging');
			return thumbDragUtils;
		};
		Thumbnail.prototype.onThumbTouchEnd = function (thumbDragUtils) {
			thumbDragUtils.isMoved = false;
			thumbDragUtils.endTime = new Date();
			this.$thumbOuter.removeClass('lg-dragging');
			var touchDuration = thumbDragUtils.endTime.valueOf() -
				thumbDragUtils.startTime.valueOf();
			var distanceXnew = thumbDragUtils.cords.endX - thumbDragUtils.cords.startX;
			var speedX = Math.abs(distanceXnew) / touchDuration;
			// Some magical numbers
			// Can be improved
			if (speedX > 0.15 &&
				thumbDragUtils.endTime.valueOf() - thumbDragUtils.touchMoveTime < 30) {
				speedX += 1;
				if (speedX > 2) {
					speedX += 1;
				}
				speedX =
					speedX +
					speedX * (Math.abs(distanceXnew) / this.thumbOuterWidth);
				this.$lgThumb.css('transition-duration', Math.min(speedX - 1, 2) + 'settings');
				distanceXnew = distanceXnew * speedX;
				this.translateX = this.getPossibleTransformX(this.translateX - distanceXnew);
				this.setTranslate(this.translateX);
			}
			else {
				this.translateX = thumbDragUtils.newTranslateX;
			}
			if (Math.abs(thumbDragUtils.cords.endX - thumbDragUtils.cords.startX) <
				this.settings.thumbnailSwipeThreshold) {
				this.thumbClickable = true;
			}
			return thumbDragUtils;
		};
		Thumbnail.prototype.getThumbHtml = function (thumb, index) {
			var slideVideoInfo = this.core.galleryItems[index].__slideVideoInfo || {};
			var thumbImg;
			if (slideVideoInfo.youtube) {
				if (this.settings.loadYouTubeThumbnail) {
					thumbImg =
						'//img.youtube.com/vi/' +
						slideVideoInfo.youtube[1] +
						'/' +
						this.settings.youTubeThumbSize +
						'.jpg';
				}
				else {
					thumbImg = thumb;
				}
			}
			else {
				thumbImg = thumb;
			}
			return "<div data-lg-item-id=\"" + index + "\" class=\"lg-thumb-item " + (index === this.core.index ? ' active' : '') + "\" \n        style=\"width:" + this.settings.thumbWidth + "px; height: " + this.settings.thumbHeight + ";\n            margin-right: " + this.settings.thumbMargin + "px;\">\n            <img data-lg-item-id=\"" + index + "\" src=\"" + thumbImg + "\" />\n        </div>";
		};
		Thumbnail.prototype.getThumbItemHtml = function (items) {
			var thumbList = '';
			for (var i = 0; i < items.length; i++) {
				thumbList += this.getThumbHtml(items[i].thumb, i);
			}
			return thumbList;
		};
		Thumbnail.prototype.setThumbItemHtml = function (items) {
			var thumbList = this.getThumbItemHtml(items);
			this.$lgThumb.html(thumbList);
		};
		Thumbnail.prototype.setAnimateThumbStyles = function () {
			if (this.settings.animateThumb) {
				this.core.outer.addClass('lg-animate-thumb');
			}
		};
		// Manage thumbnail active calss
		Thumbnail.prototype.manageActiveClassOnSlideChange = function () {
			var _this = this;
			// manage active class for thumbnail
			this.core.LGel.on(lGEvents.beforeSlide + ".thumb", function (event) {
				var $thumb = _this.core.outer.find('.lg-thumb-item');
				var index = event.detail.index;
				$thumb.removeClass('active');
				$thumb.eq(index).addClass('active');
			});
		};
		// Toggle thumbnail bar
		Thumbnail.prototype.toggleThumbBar = function () {
			var _this = this;
			if (this.settings.toggleThumb) {
				this.core.outer.addClass('lg-can-toggle');
				this.core.$toolbar.append('<button type="button" aria-label="' +
					this.settings.thumbnailPluginStrings['toggleThumbnails'] +
					'" class="lg-toggle-thumb lg-icon"></button>');
				this.core.outer
					.find('.lg-toggle-thumb')
					.first()
					.on('click.lg', function () {
						_this.core.outer.toggleClass('lg-components-open');
					});
			}
		};
		Thumbnail.prototype.thumbKeyPress = function () {
			var _this = this;
			this.$LG(window).on("keydown.lg.thumb.global" + this.core.lgId, function (e) {
				if (!_this.core.lgOpened || !_this.settings.toggleThumb)
					return;
				if (e.keyCode === 38) {
					e.preventDefault();
					_this.core.outer.addClass('lg-components-open');
				}
				else if (e.keyCode === 40) {
					e.preventDefault();
					_this.core.outer.removeClass('lg-components-open');
				}
			});
		};
		Thumbnail.prototype.destroy = function () {
			if (this.settings.thumbnail) {
				this.$LG(window).off(".lg.thumb.global" + this.core.lgId);
				this.core.LGel.off('.lg.thumb');
				this.core.LGel.off('.thumb');
				this.$thumbOuter.remove();
				this.core.outer.removeClass('lg-has-thumb');
			}
		};
		return Thumbnail;
	}());

	return Thumbnail;

})));

/*!
 * lightgallery | 2.4.0 | January 29th 2022
 * http://www.lightgalleryjs.com/
 * Copyright (c) 2020 Sachin Neravath;
 * @license GPLv3
 */

(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
		typeof define === 'function' && define.amd ? define(factory) :
			(global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.lgZoom = factory());
}(this, (function () { 'use strict';

	/*! *****************************************************************************
	Copyright (c) Microsoft Corporation.

	Permission to use, copy, modify, and/or distribute this software for any
	purpose with or without fee is hereby granted.

	THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
	REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
	INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
	LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
	OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
	PERFORMANCE OF THIS SOFTWARE.
	***************************************************************************** */

	var __assign = function() {
		__assign = Object.assign || function __assign(t) {
			for (var s, i = 1, n = arguments.length; i < n; i++) {
				s = arguments[i];
				for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
			}
			return t;
		};
		return __assign.apply(this, arguments);
	};

	var zoomSettings = {
		scale: 1,
		zoom: true,
		actualSize: true,
		showZoomInOutIcons: false,
		actualSizeIcons: {
			zoomIn: 'lg-zoom-in',
			zoomOut: 'lg-zoom-out',
		},
		enableZoomAfter: 300,
		zoomPluginStrings: {
			zoomIn: 'Zoom in',
			zoomOut: 'Zoom out',
			viewActualSize: 'View actual size',
		},
	};

	/**
	 * List of lightGallery events
	 * All events should be documented here
	 * Below interfaces are used to build the website documentations
	 * */
	var lGEvents = {
		afterAppendSlide: 'lgAfterAppendSlide',
		init: 'lgInit',
		hasVideo: 'lgHasVideo',
		containerResize: 'lgContainerResize',
		updateSlides: 'lgUpdateSlides',
		afterAppendSubHtml: 'lgAfterAppendSubHtml',
		beforeOpen: 'lgBeforeOpen',
		afterOpen: 'lgAfterOpen',
		slideItemLoad: 'lgSlideItemLoad',
		beforeSlide: 'lgBeforeSlide',
		afterSlide: 'lgAfterSlide',
		posterClick: 'lgPosterClick',
		dragStart: 'lgDragStart',
		dragMove: 'lgDragMove',
		dragEnd: 'lgDragEnd',
		beforeNextSlide: 'lgBeforeNextSlide',
		beforePrevSlide: 'lgBeforePrevSlide',
		beforeClose: 'lgBeforeClose',
		afterClose: 'lgAfterClose',
		rotateLeft: 'lgRotateLeft',
		rotateRight: 'lgRotateRight',
		flipHorizontal: 'lgFlipHorizontal',
		flipVertical: 'lgFlipVertical',
		autoplay: 'lgAutoplay',
		autoplayStart: 'lgAutoplayStart',
		autoplayStop: 'lgAutoplayStop',
	};

	var Zoom = /** @class */ (function () {
		function Zoom(instance, $LG) {
			// get lightGallery core plugin instance
			this.core = instance;
			this.$LG = $LG;
			this.settings = __assign(__assign({}, zoomSettings), this.core.settings);
			return this;
		}
		// Append Zoom controls. Actual size, Zoom-in, Zoom-out
		Zoom.prototype.buildTemplates = function () {
			var zoomIcons = this.settings.showZoomInOutIcons
				? "<button id=\"" + this.core.getIdName('lg-zoom-in') + "\" type=\"button\" aria-label=\"" + this.settings.zoomPluginStrings['zoomIn'] + "\" class=\"lg-zoom-in lg-icon\"></button><button id=\"" + this.core.getIdName('lg-zoom-out') + "\" type=\"button\" aria-label=\"" + this.settings.zoomPluginStrings['zoomIn'] + "\" class=\"lg-zoom-out lg-icon\"></button>"
				: '';
			if (this.settings.actualSize) {
				zoomIcons += "<button id=\"" + this.core.getIdName('lg-actual-size') + "\" type=\"button\" aria-label=\"" + this.settings.zoomPluginStrings['viewActualSize'] + "\" class=\"" + this.settings.actualSizeIcons.zoomIn + " lg-icon\"></button>";
			}
			this.core.outer.addClass('lg-use-transition-for-zoom');
			this.core.$toolbar.first().append(zoomIcons);
		};
		/**
		 * @desc Enable zoom option only once the image is completely loaded
		 * If zoomFromOrigin is true, Zoom is enabled once the dummy image has been inserted
		 *
		 * Zoom styles are defined under lg-zoomable CSS class.
		 */
		Zoom.prototype.enableZoom = function (event) {
			var _this = this;
			// delay will be 0 except first time
			var _speed = this.settings.enableZoomAfter + event.detail.delay;
			// set _speed value 0 if gallery opened from direct url and if it is first slide
			if (this.$LG('body').first().hasClass('lg-from-hash') &&
				event.detail.delay) {
				// will execute only once
				_speed = 0;
			}
			else {
				// Remove lg-from-hash to enable starting animation.
				this.$LG('body').first().removeClass('lg-from-hash');
			}
			this.zoomableTimeout = setTimeout(function () {
				if (!_this.isImageSlide()) {
					return;
				}
				_this.core.getSlideItem(event.detail.index).addClass('lg-zoomable');
				if (event.detail.index === _this.core.index) {
					_this.setZoomEssentials();
				}
			}, _speed + 30);
		};
		Zoom.prototype.enableZoomOnSlideItemLoad = function () {
			// Add zoomable class
			this.core.LGel.on(lGEvents.slideItemLoad + ".zoom", this.enableZoom.bind(this));
		};
		Zoom.prototype.getModifier = function (rotateValue, axis, el) {
			var originalRotate = rotateValue;
			rotateValue = Math.abs(rotateValue);
			var transformValues = this.getCurrentTransform(el);
			if (!transformValues) {
				return 1;
			}
			var modifier = 1;
			if (axis === 'X') {
				var flipHorizontalValue = Math.sign(parseFloat(transformValues[0]));
				if (rotateValue === 0 || rotateValue === 180) {
					modifier = 1;
				}
				else if (rotateValue === 90) {
					if ((originalRotate === -90 && flipHorizontalValue === 1) ||
						(originalRotate === 90 && flipHorizontalValue === -1)) {
						modifier = -1;
					}
					else {
						modifier = 1;
					}
				}
				modifier = modifier * flipHorizontalValue;
			}
			else {
				var flipVerticalValue = Math.sign(parseFloat(transformValues[3]));
				if (rotateValue === 0 || rotateValue === 180) {
					modifier = 1;
				}
				else if (rotateValue === 90) {
					var sinX = parseFloat(transformValues[1]);
					var sinMinusX = parseFloat(transformValues[2]);
					modifier = Math.sign(sinX * sinMinusX * originalRotate * flipVerticalValue);
				}
				modifier = modifier * flipVerticalValue;
			}
			return modifier;
		};
		Zoom.prototype.getImageSize = function ($image, rotateValue, axis) {
			var imageSizes = {
				y: 'offsetHeight',
				x: 'offsetWidth',
			};
			if (Math.abs(rotateValue) === 90) {
				// Swap axis
				if (axis === 'x') {
					axis = 'y';
				}
				else {
					axis = 'x';
				}
			}
			return $image[imageSizes[axis]];
		};
		Zoom.prototype.getDragCords = function (e, rotateValue) {
			if (rotateValue === 90) {
				return {
					x: e.pageY,
					y: e.pageX,
				};
			}
			else {
				return {
					x: e.pageX,
					y: e.pageY,
				};
			}
		};
		Zoom.prototype.getSwipeCords = function (e, rotateValue) {
			var x = e.targetTouches[0].pageX;
			var y = e.targetTouches[0].pageY;
			if (rotateValue === 90) {
				return {
					x: y,
					y: x,
				};
			}
			else {
				return {
					x: x,
					y: y,
				};
			}
		};
		Zoom.prototype.getDragAllowedAxises = function (rotateValue, scale) {
			scale = scale || this.scale || 1;
			var allowY = this.imageYSize * scale > this.containerRect.height;
			var allowX = this.imageXSize * scale > this.containerRect.width;
			if (rotateValue === 90) {
				return {
					allowX: allowY,
					allowY: allowX,
				};
			}
			else {
				return {
					allowX: allowX,
					allowY: allowY,
				};
			}
		};
		/**
		 *
		 * @param {Element} el
		 * @return matrix(cos(X), sin(X), -sin(X), cos(X), 0, 0);
		 * Get the current transform value
		 */
		Zoom.prototype.getCurrentTransform = function (el) {
			if (!el) {
				return;
			}
			var st = window.getComputedStyle(el, null);
			var tm = st.getPropertyValue('-webkit-transform') ||
				st.getPropertyValue('-moz-transform') ||
				st.getPropertyValue('-ms-transform') ||
				st.getPropertyValue('-o-transform') ||
				st.getPropertyValue('transform') ||
				'none';
			if (tm !== 'none') {
				return tm.split('(')[1].split(')')[0].split(',');
			}
			return;
		};
		Zoom.prototype.getCurrentRotation = function (el) {
			if (!el) {
				return 0;
			}
			var values = this.getCurrentTransform(el);
			if (values) {
				return Math.round(Math.atan2(parseFloat(values[1]), parseFloat(values[0])) *
					(180 / Math.PI));
				// If you want rotate in 360
				//return (angle < 0 ? angle + 360 : angle);
			}
			return 0;
		};
		Zoom.prototype.setZoomEssentials = function () {
			var $image = this.core
				.getSlideItem(this.core.index)
				.find('.lg-image')
				.first();
			var rotateEl = this.core
				.getSlideItem(this.core.index)
				.find('.lg-img-rotate')
				.first()
				.get();
			this.rotateValue = this.getCurrentRotation(rotateEl);
			this.imageYSize = this.getImageSize($image.get(), this.rotateValue, 'y');
			this.imageXSize = this.getImageSize($image.get(), this.rotateValue, 'x');
			this.containerRect = this.core.outer.get().getBoundingClientRect();
			this.modifierX = this.getModifier(this.rotateValue, 'X', rotateEl);
			this.modifierY = this.getModifier(this.rotateValue, 'Y', rotateEl);
		};
		/**
		 * @desc Image zoom
		 * Translate the wrap and scale the image to get better user experience
		 *
		 * @param {String} scale - Zoom decrement/increment value
		 */
		Zoom.prototype.zoomImage = function (scale) {
			// Find offset manually to avoid issue after zoom
			var offsetX = (this.containerRect.width - this.imageXSize) / 2 +
				this.containerRect.left;
			var _a = this.core.mediaContainerPosition, top = _a.top, bottom = _a.bottom;
			var topBottomSpacing = Math.abs(top - bottom) / 2;
			var offsetY = (this.containerRect.height -
				this.imageYSize -
				topBottomSpacing * this.modifierX) /
				2 +
				this.scrollTop +
				this.containerRect.top;
			var originalX;
			var originalY;
			if (scale === 1) {
				this.positionChanged = false;
			}
			var dragAllowedAxises = this.getDragAllowedAxises(Math.abs(this.rotateValue), scale);
			var allowY = dragAllowedAxises.allowY, allowX = dragAllowedAxises.allowX;
			if (this.positionChanged) {
				originalX = this.left / (this.scale - 1);
				originalY = this.top / (this.scale - 1);
				this.pageX = Math.abs(originalX) + offsetX;
				this.pageY = Math.abs(originalY) + offsetY;
				this.positionChanged = false;
			}
			var possibleSwipeCords = this.getPossibleSwipeDragCords(this.rotateValue, scale);
			var _x = offsetX - this.pageX;
			var _y = offsetY - this.pageY;
			var x = (scale - 1) * _x;
			var y = (scale - 1) * _y;
			if (allowX) {
				if (this.isBeyondPossibleLeft(x, possibleSwipeCords.minX)) {
					x = possibleSwipeCords.minX;
				}
				else if (this.isBeyondPossibleRight(x, possibleSwipeCords.maxX)) {
					x = possibleSwipeCords.maxX;
				}
			}
			else {
				if (scale > 1) {
					if (x < possibleSwipeCords.minX) {
						x = possibleSwipeCords.minX;
					}
					else if (x > possibleSwipeCords.maxX) {
						x = possibleSwipeCords.maxX;
					}
				}
			}
			if (allowY) {
				if (this.isBeyondPossibleTop(y, possibleSwipeCords.minY)) {
					y = possibleSwipeCords.minY;
				}
				else if (this.isBeyondPossibleBottom(y, possibleSwipeCords.maxY)) {
					y = possibleSwipeCords.maxY;
				}
			}
			else {
				// If the translate value based on index of beyond the viewport, utilize the available space to prevent image being cut out
				if (scale > 1) {
					//If image goes beyond viewport top, use the minim possible translate value
					if (y < possibleSwipeCords.minY) {
						y = possibleSwipeCords.minY;
					}
					else if (y > possibleSwipeCords.maxY) {
						y = possibleSwipeCords.maxY;
					}
				}
			}
			this.setZoomStyles({
				x: x,
				y: y,
				scale: scale,
			});
		};
		/**
		 * @desc apply scale3d to image and translate to image wrap
		 * @param {style} X,Y and scale
		 */
		Zoom.prototype.setZoomStyles = function (style) {
			var $image = this.core
				.getSlideItem(this.core.index)
				.find('.lg-image')
				.first();
			var $dummyImage = this.core.outer
				.find('.lg-current .lg-dummy-img')
				.first();
			var $imageWrap = $image.parent();
			this.scale = style.scale;
			$image.css('transform', 'scale3d(' + style.scale + ', ' + style.scale + ', 1)');
			$dummyImage.css('transform', 'scale3d(' + style.scale + ', ' + style.scale + ', 1)');
			var transform = 'translate3d(' + style.x + 'px, ' + style.y + 'px, 0)';
			$imageWrap.css('transform', transform);
			this.left = style.x;
			this.top = style.y;
		};
		/**
		 * @param index - Index of the current slide
		 * @param event - event will be available only if the function is called on clicking/taping the imags
		 */
		Zoom.prototype.setActualSize = function (index, event) {
			var _this = this;
			// Allow zoom only on image
			if (!this.isImageSlide() ||
				this.core.outer.hasClass('lg-first-slide-loading')) {
				return;
			}
			var scale = this.getCurrentImageActualSizeScale();
			if (this.core.outer.hasClass('lg-zoomed')) {
				this.scale = 1;
			}
			else {
				this.scale = this.getScale(scale);
			}
			this.setPageCords(event);
			this.beginZoom(this.scale);
			this.zoomImage(this.scale);
			setTimeout(function () {
				_this.core.outer.removeClass('lg-grabbing').addClass('lg-grab');
			}, 10);
		};
		Zoom.prototype.getNaturalWidth = function (index) {
			var $image = this.core.getSlideItem(index).find('.lg-image').first();
			var naturalWidth = this.core.galleryItems[index].width;
			return naturalWidth
				? parseFloat(naturalWidth)
				: $image.get().naturalWidth;
		};
		Zoom.prototype.getActualSizeScale = function (naturalWidth, width) {
			var _scale;
			var scale;
			if (naturalWidth > width) {
				_scale = naturalWidth / width;
				scale = _scale || 2;
			}
			else {
				scale = 1;
			}
			return scale;
		};
		Zoom.prototype.getCurrentImageActualSizeScale = function () {
			var $image = this.core
				.getSlideItem(this.core.index)
				.find('.lg-image')
				.first();
			var width = $image.get().offsetWidth;
			var naturalWidth = this.getNaturalWidth(this.core.index) || width;
			return this.getActualSizeScale(naturalWidth, width);
		};
		Zoom.prototype.getPageCords = function (event) {
			var cords = {};
			if (event) {
				cords.x = event.pageX || event.targetTouches[0].pageX;
				cords.y = event.pageY || event.targetTouches[0].pageY;
			}
			else {
				var containerRect = this.core.outer.get().getBoundingClientRect();
				cords.x = containerRect.width / 2 + containerRect.left;
				cords.y =
					containerRect.height / 2 + this.scrollTop + containerRect.top;
			}
			return cords;
		};
		Zoom.prototype.setPageCords = function (event) {
			var pageCords = this.getPageCords(event);
			this.pageX = pageCords.x;
			this.pageY = pageCords.y;
		};
		// If true, zoomed - in else zoomed out
		Zoom.prototype.beginZoom = function (scale) {
			this.core.outer.removeClass('lg-zoom-drag-transition lg-zoom-dragging');
			if (scale > 1) {
				this.core.outer.addClass('lg-zoomed');
				var $actualSize = this.core.getElementById('lg-actual-size');
				$actualSize
					.removeClass(this.settings.actualSizeIcons.zoomIn)
					.addClass(this.settings.actualSizeIcons.zoomOut);
			}
			else {
				this.resetZoom();
			}
			return scale > 1;
		};
		Zoom.prototype.getScale = function (scale) {
			var actualSizeScale = this.getCurrentImageActualSizeScale();
			if (scale < 1) {
				scale = 1;
			}
			else if (scale > actualSizeScale) {
				scale = actualSizeScale;
			}
			return scale;
		};
		Zoom.prototype.init = function () {
			var _this = this;
			if (!this.settings.zoom) {
				return;
			}
			this.buildTemplates();
			this.enableZoomOnSlideItemLoad();
			var tapped = null;
			this.core.outer.on('dblclick.lg', function (event) {
				if (!_this.$LG(event.target).hasClass('lg-image')) {
					return;
				}
				_this.setActualSize(_this.core.index, event);
			});
			this.core.outer.on('touchstart.lg', function (event) {
				var $target = _this.$LG(event.target);
				if (event.targetTouches.length === 1 &&
					$target.hasClass('lg-image')) {
					if (!tapped) {
						tapped = setTimeout(function () {
							tapped = null;
						}, 300);
					}
					else {
						clearTimeout(tapped);
						tapped = null;
						event.preventDefault();
						_this.setActualSize(_this.core.index, event);
					}
				}
			});
			// Update zoom on resize and orientationchange
			this.core.LGel.on(lGEvents.containerResize + ".zoom " + lGEvents.rotateRight + ".zoom " + lGEvents.rotateLeft + ".zoom " + lGEvents.flipHorizontal + ".zoom " + lGEvents.flipVertical + ".zoom", function () {
				if (!_this.core.lgOpened || !_this.isImageSlide())
					return;
				_this.setPageCords();
				_this.setZoomEssentials();
				_this.zoomImage(_this.scale);
			});
			// Update zoom on resize and orientationchange
			this.$LG(window).on("scroll.lg.zoom.global" + this.core.lgId, function () {
				if (!_this.core.lgOpened)
					return;
				_this.scrollTop = _this.$LG(window).scrollTop();
			});
			this.core.getElementById('lg-zoom-out').on('click.lg', function () {
				if (_this.core.outer.find('.lg-current .lg-image').get()) {
					_this.scale -= _this.settings.scale;
					_this.scale = _this.getScale(_this.scale);
					_this.beginZoom(_this.scale);
					_this.zoomImage(_this.scale);
				}
			});
			this.core.getElementById('lg-zoom-in').on('click.lg', function () {
				_this.zoomIn();
			});
			this.core.getElementById('lg-actual-size').on('click.lg', function () {
				_this.setActualSize(_this.core.index);
			});
			this.core.LGel.on(lGEvents.beforeOpen + ".zoom", function () {
				_this.core.outer.find('.lg-item').removeClass('lg-zoomable');
			});
			this.core.LGel.on(lGEvents.afterOpen + ".zoom", function () {
				_this.scrollTop = _this.$LG(window).scrollTop();
				// Set the initial value center
				_this.pageX = _this.core.outer.width() / 2;
				_this.pageY = _this.core.outer.height() / 2 + _this.scrollTop;
				_this.scale = 1;
			});
			// Reset zoom on slide change
			this.core.LGel.on(lGEvents.afterSlide + ".zoom", function (event) {
				var prevIndex = event.detail.prevIndex;
				_this.scale = 1;
				_this.positionChanged = false;
				_this.resetZoom(prevIndex);
				if (_this.isImageSlide()) {
					_this.setZoomEssentials();
				}
			});
			// Drag option after zoom
			this.zoomDrag();
			this.pinchZoom();
			this.zoomSwipe();
			// Store the zoomable timeout value just to clear it while closing
			this.zoomableTimeout = false;
			this.positionChanged = false;
		};
		Zoom.prototype.zoomIn = function (scale) {
			// Allow zoom only on image
			if (!this.isImageSlide()) {
				return;
			}
			if (scale) {
				this.scale = scale;
			}
			else {
				this.scale += this.settings.scale;
			}
			this.scale = this.getScale(this.scale);
			this.beginZoom(this.scale);
			this.zoomImage(this.scale);
		};
		// Reset zoom effect
		Zoom.prototype.resetZoom = function (index) {
			this.core.outer.removeClass('lg-zoomed lg-zoom-drag-transition');
			var $actualSize = this.core.getElementById('lg-actual-size');
			var $item = this.core.getSlideItem(index !== undefined ? index : this.core.index);
			$actualSize
				.removeClass(this.settings.actualSizeIcons.zoomOut)
				.addClass(this.settings.actualSizeIcons.zoomIn);
			$item.find('.lg-img-wrap').first().removeAttr('style');
			$item.find('.lg-image').first().removeAttr('style');
			this.scale = 1;
			this.left = 0;
			this.top = 0;
			// Reset pagx pagy values to center
			this.setPageCords();
		};
		Zoom.prototype.getTouchDistance = function (e) {
			return Math.sqrt((e.targetTouches[0].pageX - e.targetTouches[1].pageX) *
				(e.targetTouches[0].pageX - e.targetTouches[1].pageX) +
				(e.targetTouches[0].pageY - e.targetTouches[1].pageY) *
				(e.targetTouches[0].pageY - e.targetTouches[1].pageY));
		};
		Zoom.prototype.pinchZoom = function () {
			var _this = this;
			var startDist = 0;
			var pinchStarted = false;
			var initScale = 1;
			var $item = this.core.getSlideItem(this.core.index);
			this.core.$inner.on('touchstart.lg', function (e) {
				$item = _this.core.getSlideItem(_this.core.index);
				if (!_this.isImageSlide()) {
					return;
				}
				if (e.targetTouches.length === 2 &&
					!_this.core.outer.hasClass('lg-first-slide-loading') &&
					(_this.$LG(e.target).hasClass('lg-item') ||
						$item.get().contains(e.target))) {
					initScale = _this.scale || 1;
					_this.core.outer.removeClass('lg-zoom-drag-transition lg-zoom-dragging');
					_this.core.touchAction = 'pinch';
					startDist = _this.getTouchDistance(e);
				}
			});
			this.core.$inner.on('touchmove.lg', function (e) {
				if (e.targetTouches.length === 2 &&
					_this.core.touchAction === 'pinch' &&
					(_this.$LG(e.target).hasClass('lg-item') ||
						$item.get().contains(e.target))) {
					e.preventDefault();
					var endDist = _this.getTouchDistance(e);
					var distance = startDist - endDist;
					if (!pinchStarted && Math.abs(distance) > 5) {
						pinchStarted = true;
					}
					if (pinchStarted) {
						_this.scale = Math.max(1, initScale + -distance * 0.008);
						_this.zoomImage(_this.scale);
					}
				}
			});
			this.core.$inner.on('touchend.lg', function (e) {
				if (_this.core.touchAction === 'pinch' &&
					(_this.$LG(e.target).hasClass('lg-item') ||
						$item.get().contains(e.target))) {
					pinchStarted = false;
					startDist = 0;
					if (_this.scale <= 1) {
						_this.resetZoom();
					}
					else {
						_this.scale = _this.getScale(_this.scale);
						_this.zoomImage(_this.scale);
						_this.core.outer.addClass('lg-zoomed');
					}
					_this.core.touchAction = undefined;
				}
			});
		};
		Zoom.prototype.touchendZoom = function (startCoords, endCoords, allowX, allowY, touchDuration, rotateValue) {
			var distanceXnew = endCoords.x - startCoords.x;
			var distanceYnew = endCoords.y - startCoords.y;
			var speedX = Math.abs(distanceXnew) / touchDuration + 1;
			var speedY = Math.abs(distanceYnew) / touchDuration + 1;
			if (speedX > 2) {
				speedX += 1;
			}
			if (speedY > 2) {
				speedY += 1;
			}
			distanceXnew = distanceXnew * speedX;
			distanceYnew = distanceYnew * speedY;
			var _LGel = this.core
				.getSlideItem(this.core.index)
				.find('.lg-img-wrap')
				.first();
			var distance = {};
			distance.x = this.left + distanceXnew * this.modifierX;
			distance.y = this.top + distanceYnew * this.modifierY;
			var possibleSwipeCords = this.getPossibleSwipeDragCords(rotateValue);
			if (Math.abs(distanceXnew) > 15 || Math.abs(distanceYnew) > 15) {
				if (allowY) {
					if (this.isBeyondPossibleTop(distance.y, possibleSwipeCords.minY)) {
						distance.y = possibleSwipeCords.minY;
					}
					else if (this.isBeyondPossibleBottom(distance.y, possibleSwipeCords.maxY)) {
						distance.y = possibleSwipeCords.maxY;
					}
				}
				if (allowX) {
					if (this.isBeyondPossibleLeft(distance.x, possibleSwipeCords.minX)) {
						distance.x = possibleSwipeCords.minX;
					}
					else if (this.isBeyondPossibleRight(distance.x, possibleSwipeCords.maxX)) {
						distance.x = possibleSwipeCords.maxX;
					}
				}
				if (allowY) {
					this.top = distance.y;
				}
				else {
					distance.y = this.top;
				}
				if (allowX) {
					this.left = distance.x;
				}
				else {
					distance.x = this.left;
				}
				this.setZoomSwipeStyles(_LGel, distance);
				this.positionChanged = true;
			}
		};
		Zoom.prototype.getZoomSwipeCords = function (startCoords, endCoords, allowX, allowY, possibleSwipeCords) {
			var distance = {};
			if (allowY) {
				distance.y =
					this.top + (endCoords.y - startCoords.y) * this.modifierY;
				if (this.isBeyondPossibleTop(distance.y, possibleSwipeCords.minY)) {
					var diffMinY = possibleSwipeCords.minY - distance.y;
					distance.y = possibleSwipeCords.minY - diffMinY / 6;
				}
				else if (this.isBeyondPossibleBottom(distance.y, possibleSwipeCords.maxY)) {
					var diffMaxY = distance.y - possibleSwipeCords.maxY;
					distance.y = possibleSwipeCords.maxY + diffMaxY / 6;
				}
			}
			else {
				distance.y = this.top;
			}
			if (allowX) {
				distance.x =
					this.left + (endCoords.x - startCoords.x) * this.modifierX;
				if (this.isBeyondPossibleLeft(distance.x, possibleSwipeCords.minX)) {
					var diffMinX = possibleSwipeCords.minX - distance.x;
					distance.x = possibleSwipeCords.minX - diffMinX / 6;
				}
				else if (this.isBeyondPossibleRight(distance.x, possibleSwipeCords.maxX)) {
					var difMaxX = distance.x - possibleSwipeCords.maxX;
					distance.x = possibleSwipeCords.maxX + difMaxX / 6;
				}
			}
			else {
				distance.x = this.left;
			}
			return distance;
		};
		Zoom.prototype.isBeyondPossibleLeft = function (x, minX) {
			return x >= minX;
		};
		Zoom.prototype.isBeyondPossibleRight = function (x, maxX) {
			return x <= maxX;
		};
		Zoom.prototype.isBeyondPossibleTop = function (y, minY) {
			return y >= minY;
		};
		Zoom.prototype.isBeyondPossibleBottom = function (y, maxY) {
			return y <= maxY;
		};
		Zoom.prototype.isImageSlide = function () {
			var currentItem = this.core.galleryItems[this.core.index];
			return this.core.getSlideType(currentItem) === 'image';
		};
		Zoom.prototype.getPossibleSwipeDragCords = function (rotateValue, scale) {
			var dataScale = scale || this.scale || 1;
			var elDataScale = Math.abs(dataScale);
			var _a = this.core.mediaContainerPosition, top = _a.top, bottom = _a.bottom;
			var topBottomSpacing = Math.abs(top - bottom) / 2;
			var minY = (this.imageYSize - this.containerRect.height) / 2 +
				topBottomSpacing * this.modifierX;
			var maxY = this.containerRect.height - this.imageYSize * elDataScale + minY;
			var minX = (this.imageXSize - this.containerRect.width) / 2;
			var maxX = this.containerRect.width - this.imageXSize * elDataScale + minX;
			var possibleSwipeCords = {
				minY: minY,
				maxY: maxY,
				minX: minX,
				maxX: maxX,
			};
			if (Math.abs(rotateValue) === 90) {
				possibleSwipeCords = {
					minY: minX,
					maxY: maxX,
					minX: minY,
					maxX: maxY,
				};
			}
			return possibleSwipeCords;
		};
		Zoom.prototype.setZoomSwipeStyles = function (LGel, distance) {
			LGel.css('transform', 'translate3d(' + distance.x + 'px, ' + distance.y + 'px, 0)');
		};
		Zoom.prototype.zoomSwipe = function () {
			var _this = this;
			var startCoords = {};
			var endCoords = {};
			var isMoved = false;
			// Allow x direction drag
			var allowX = false;
			// Allow Y direction drag
			var allowY = false;
			var startTime = new Date();
			var endTime = new Date();
			var possibleSwipeCords;
			var _LGel;
			var $item = this.core.getSlideItem(this.core.index);
			this.core.$inner.on('touchstart.lg', function (e) {
				// Allow zoom only on image
				if (!_this.isImageSlide()) {
					return;
				}
				$item = _this.core.getSlideItem(_this.core.index);
				if ((_this.$LG(e.target).hasClass('lg-item') ||
					$item.get().contains(e.target)) &&
					e.targetTouches.length === 1 &&
					_this.core.outer.hasClass('lg-zoomed')) {
					e.preventDefault();
					startTime = new Date();
					_this.core.touchAction = 'zoomSwipe';
					_LGel = _this.core
						.getSlideItem(_this.core.index)
						.find('.lg-img-wrap')
						.first();
					var dragAllowedAxises = _this.getDragAllowedAxises(Math.abs(_this.rotateValue));
					allowY = dragAllowedAxises.allowY;
					allowX = dragAllowedAxises.allowX;
					if (allowX || allowY) {
						startCoords = _this.getSwipeCords(e, Math.abs(_this.rotateValue));
					}
					possibleSwipeCords = _this.getPossibleSwipeDragCords(_this.rotateValue);
					// reset opacity and transition duration
					_this.core.outer.addClass('lg-zoom-dragging lg-zoom-drag-transition');
				}
			});
			this.core.$inner.on('touchmove.lg', function (e) {
				if (e.targetTouches.length === 1 &&
					_this.core.touchAction === 'zoomSwipe' &&
					(_this.$LG(e.target).hasClass('lg-item') ||
						$item.get().contains(e.target))) {
					e.preventDefault();
					_this.core.touchAction = 'zoomSwipe';
					endCoords = _this.getSwipeCords(e, Math.abs(_this.rotateValue));
					var distance = _this.getZoomSwipeCords(startCoords, endCoords, allowX, allowY, possibleSwipeCords);
					if (Math.abs(endCoords.x - startCoords.x) > 15 ||
						Math.abs(endCoords.y - startCoords.y) > 15) {
						isMoved = true;
						_this.setZoomSwipeStyles(_LGel, distance);
					}
				}
			});
			this.core.$inner.on('touchend.lg', function (e) {
				if (_this.core.touchAction === 'zoomSwipe' &&
					(_this.$LG(e.target).hasClass('lg-item') ||
						$item.get().contains(e.target))) {
					_this.core.touchAction = undefined;
					_this.core.outer.removeClass('lg-zoom-dragging');
					if (!isMoved) {
						return;
					}
					isMoved = false;
					endTime = new Date();
					var touchDuration = endTime.valueOf() - startTime.valueOf();
					_this.touchendZoom(startCoords, endCoords, allowX, allowY, touchDuration, _this.rotateValue);
				}
			});
		};
		Zoom.prototype.zoomDrag = function () {
			var _this = this;
			var startCoords = {};
			var endCoords = {};
			var isDragging = false;
			var isMoved = false;
			// Allow x direction drag
			var allowX = false;
			// Allow Y direction drag
			var allowY = false;
			var startTime;
			var endTime;
			var possibleSwipeCords;
			var _LGel;
			this.core.outer.on('mousedown.lg.zoom', function (e) {
				// Allow zoom only on image
				if (!_this.isImageSlide()) {
					return;
				}
				var $item = _this.core.getSlideItem(_this.core.index);
				if (_this.$LG(e.target).hasClass('lg-item') ||
					$item.get().contains(e.target)) {
					startTime = new Date();
					_LGel = _this.core
						.getSlideItem(_this.core.index)
						.find('.lg-img-wrap')
						.first();
					var dragAllowedAxises = _this.getDragAllowedAxises(Math.abs(_this.rotateValue));
					allowY = dragAllowedAxises.allowY;
					allowX = dragAllowedAxises.allowX;
					if (_this.core.outer.hasClass('lg-zoomed')) {
						if (_this.$LG(e.target).hasClass('lg-object') &&
							(allowX || allowY)) {
							e.preventDefault();
							startCoords = _this.getDragCords(e, Math.abs(_this.rotateValue));
							possibleSwipeCords = _this.getPossibleSwipeDragCords(_this.rotateValue);
							isDragging = true;
							// ** Fix for webkit cursor issue https://code.google.com/p/chromium/issues/detail?id=26723
							_this.core.outer.get().scrollLeft += 1;
							_this.core.outer.get().scrollLeft -= 1;
							_this.core.outer
								.removeClass('lg-grab')
								.addClass('lg-grabbing lg-zoom-drag-transition lg-zoom-dragging');
							// reset opacity and transition duration
						}
					}
				}
			});
			this.$LG(window).on("mousemove.lg.zoom.global" + this.core.lgId, function (e) {
				if (isDragging) {
					isMoved = true;
					endCoords = _this.getDragCords(e, Math.abs(_this.rotateValue));
					var distance = _this.getZoomSwipeCords(startCoords, endCoords, allowX, allowY, possibleSwipeCords);
					_this.setZoomSwipeStyles(_LGel, distance);
				}
			});
			this.$LG(window).on("mouseup.lg.zoom.global" + this.core.lgId, function (e) {
				if (isDragging) {
					endTime = new Date();
					isDragging = false;
					_this.core.outer.removeClass('lg-zoom-dragging');
					// Fix for chrome mouse move on click
					if (isMoved &&
						(startCoords.x !== endCoords.x ||
							startCoords.y !== endCoords.y)) {
						endCoords = _this.getDragCords(e, Math.abs(_this.rotateValue));
						var touchDuration = endTime.valueOf() - startTime.valueOf();
						_this.touchendZoom(startCoords, endCoords, allowX, allowY, touchDuration, _this.rotateValue);
					}
					isMoved = false;
				}
				_this.core.outer.removeClass('lg-grabbing').addClass('lg-grab');
			});
		};
		Zoom.prototype.closeGallery = function () {
			this.resetZoom();
		};
		Zoom.prototype.destroy = function () {
			// Unbind all events added by lightGallery zoom plugin
			this.$LG(window).off(".lg.zoom.global" + this.core.lgId);
			this.core.LGel.off('.lg.zoom');
			this.core.LGel.off('.zoom');
			clearTimeout(this.zoomableTimeout);
			this.zoomableTimeout = false;
		};
		return Zoom;
	}());

	return Zoom;

})));
