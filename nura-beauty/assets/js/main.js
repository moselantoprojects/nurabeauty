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


/* ===== NURA v1.1.0 - Hero slider ===== */
(function(){var d=document;function r(f){if(d.readyState!=="loading"){f();}else{d.addEventListener("DOMContentLoaded",f);}}
r(function(){
	var root=d.querySelector("[data-nura-slider]");if(!root){return;}
	var slides=[].slice.call(root.querySelectorAll(".nura-slide"));if(slides.length<2){return;}
	var dotsWrap=root.querySelector("[data-slider-dots]");var cur=0,timer=null;
	var dots=slides.map(function(s,i){var btn=d.createElement("button");btn.type="button";btn.setAttribute("aria-label","Go to slide "+(i+1));if(i===0){btn.className="is-active";}btn.addEventListener("click",function(){go(i);reset();});if(dotsWrap){dotsWrap.appendChild(btn);}return btn;});
	function go(i){slides[cur].classList.remove("is-active");if(dots[cur]){dots[cur].classList.remove("is-active");}cur=(i+slides.length)%slides.length;slides[cur].classList.add("is-active");if(dots[cur]){dots[cur].classList.add("is-active");}}
	function next(){go(cur+1);}function prev(){go(cur-1);}
	var bn=root.querySelector("[data-slider-next]"),bp=root.querySelector("[data-slider-prev]");
	if(bn){bn.addEventListener("click",function(){next();reset();});}
	if(bp){bp.addEventListener("click",function(){prev();reset();});}
	function start(){timer=window.setInterval(next,6500);}function reset(){window.clearInterval(timer);start();}
	root.addEventListener("mouseenter",function(){window.clearInterval(timer);});
	root.addEventListener("mouseleave",start);
	start();
});})();

/* ===== NURA v1.1.0 - Product rails ===== */
(function(){var d=document;function r(f){if(d.readyState!=="loading"){f();}else{d.addEventListener("DOMContentLoaded",f);}}
r(function(){[].slice.call(d.querySelectorAll("[data-nura-rail]")).forEach(function(rail){
	var track=rail.querySelector("ul.products");if(!track){return;}
	var p=rail.querySelector("[data-rail-prev]"),n=rail.querySelector("[data-rail-next]");
	function step(){var card=track.querySelector("li.product");var w=card?card.getBoundingClientRect().width+24:280;return w*2;}
	if(p){p.addEventListener("click",function(){track.scrollBy({left:-step(),behavior:"smooth"});});}
	if(n){n.addEventListener("click",function(){track.scrollBy({left:step(),behavior:"smooth"});});}
});});})();

/* ===== NURA v1.1.0 - Book a fitting -> WhatsApp ===== */
(function(){var d=document;function r(f){if(d.readyState!=="loading"){f();}else{d.addEventListener("DOMContentLoaded",f);}}
r(function(){var form=d.querySelector("[data-nura-book]");if(!form){return;}
	form.addEventListener("submit",function(e){e.preventDefault();
		var wa=form.getAttribute("data-wa")||"";
		var g=function(k){var el=form.querySelector('[name="'+k+'"]');return el?el.value:"";};
		var parts=["Hello NURA, I would like to book: "+g("service"),"Name: "+g("name"),"Phone: "+g("phone"),"Preferred date: "+g("date"),"Notes: "+g("note")];
		var text=encodeURIComponent(parts.join("\n"));
		var base=wa;
		if(base.indexOf("wa.me")===-1&&base.indexOf("whatsapp")===-1){var digits=(wa||"").replace(/[^0-9]/g,"");base=digits?("https://wa.me/"+digits):"https://wa.me/";}
		var url=base+(base.indexOf("?")===-1?"?":"&")+"text="+text;
		window.open(url,"_blank");
	});
});})();
