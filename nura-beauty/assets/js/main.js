/* NURA Beauty - lightweight, dependency-free interactions.
   Kept small and deferred to protect Core Web Vitals (INP/TBT). */
(function () {
	'use strict';

	var d = document;

	function ready(fn){ if(d.readyState!=='loading'){fn();}else{d.addEventListener('DOMContentLoaded',fn);} }

	ready(function () {
		// Header shadow on scroll.
		var header = d.getElementById('site-header');
		if (header) {
			var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 10); };
			window.addEventListener('scroll', onScroll, { passive: true });
			onScroll();
		}

		// Mobile drawer.
		var burger  = d.querySelector('[data-nura-drawer]');
		var panel   = d.querySelector('[data-nura-drawer-panel]');
		var overlay = d.querySelector('[data-nura-overlay]');
		function closeDrawer(){ if(panel){panel.classList.remove('is-open');panel.setAttribute('aria-hidden','true');} if(overlay){overlay.classList.remove('is-open');} }
		function openDrawer(){ if(panel){panel.classList.add('is-open');panel.setAttribute('aria-hidden','false');} if(overlay){overlay.classList.add('is-open');} }
		if (burger) { burger.addEventListener('click', openDrawer); }
		if (overlay) { overlay.addEventListener('click', closeDrawer); }
		d.addEventListener('keyup', function (e) { if (e.key === 'Escape') { closeDrawer(); } });

		// Scroll reveal.
		var reveal = d.querySelectorAll('.nura-reveal');
		if ('IntersectionObserver' in window && reveal.length) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } });
			}, { threshold: 0.12 });
			reveal.forEach(function (el) { io.observe(el); });
		} else {
			reveal.forEach(function (el) { el.classList.add('is-in'); });
		}

		// Sticky add-to-cart on single product.
		var sticky = d.querySelector('.nura-sticky-atc');
		var atc = d.querySelector('form.cart');
		if (sticky && atc && 'IntersectionObserver' in window) {
			var so = new IntersectionObserver(function (entries) {
				entries.forEach(function (en) { sticky.classList.toggle('is-visible', !en.isIntersecting); });
			}, { threshold: 0 });
			so.observe(atc);
			sticky.addEventListener('click', function () { atc.scrollIntoView({ behavior: 'smooth', block: 'center' }); });
		}
	});
})();


/* Category carousel arrows */
(function(){var d=document;function r(f){if(d.readyState!=="loading"){f();}else{d.addEventListener("DOMContentLoaded",f);}}
r(function(){var row=d.querySelector("[data-nura-cats]");if(!row){return;}
var p=d.querySelector("[data-nura-cats-prev]"),n=d.querySelector("[data-nura-cats-next]");
function step(){return Math.max(row.clientWidth*0.85,240);}
if(p){p.addEventListener("click",function(){row.scrollBy({left:-step(),behavior:"smooth"});});}
if(n){n.addEventListener("click",function(){row.scrollBy({left:step(),behavior:"smooth"});});}
});})();
