/* project-ui.js — project detail interactions
 * Port of src/components/project-detail-ui.tsx (ProjectNav, AmenitySlider,
 * FloorPlanPicker, ProjectGallery lightbox). */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    /* ---------- ProjectNav: sticky bar + scroll-spy ---------- */
    var bar = document.querySelector("[data-project-nav-bar]");
    if (bar) {
      var sentinel = document.createElement("div");
      bar.parentElement.insertBefore(sentinel, bar);
      var io = new IntersectionObserver(function (entries) {
        bar.classList.toggle("is-stuck", !entries[0].isIntersecting);
      }, { threshold: 0 });
      io.observe(sentinel);

      var links = {};
      bar.querySelectorAll("[data-project-nav-id]").forEach(function (a) {
        links[a.getAttribute("data-project-nav-id")] = a;
      });
      var sections = Object.keys(links).map(function (id) {
        return document.getElementById(id);
      }).filter(Boolean);
      if (sections.length) {
        var io2 = new IntersectionObserver(function (entries) {
          entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            Object.keys(links).forEach(function (k) {
              links[k].classList.toggle("active", k === e.target.id);
            });
          });
        }, { rootMargin: "-45% 0px -50% 0px", threshold: 0 });
        sections.forEach(function (s) { io2.observe(s); });
      }
    }

    /* ---------- AmenitySlider arrows ---------- */
    var track = document.querySelector("[data-amenity-track]");
    if (track) {
      var prev = track.closest(".amenities-slider-wrap").querySelector("[data-amenity-prev]");
      var next = track.closest(".amenities-slider-wrap").querySelector("[data-amenity-next]");
      function update() {
        if (prev) prev.classList.toggle("disabled", track.scrollLeft <= 10);
        if (next) next.classList.toggle("disabled", track.scrollLeft + track.clientWidth >= track.scrollWidth - 10);
      }
      track.addEventListener("scroll", update, { passive: true });
      window.addEventListener("resize", update);
      update();
      if (prev) prev.addEventListener("click", function () {
        track.scrollBy({ left: -Math.round(track.clientWidth * 0.9), behavior: "smooth" });
      });
      if (next) next.addEventListener("click", function () {
        track.scrollBy({ left: Math.round(track.clientWidth * 0.9), behavior: "smooth" });
      });
    }

    /* ---------- FloorPlanPicker ---------- */
    var img = document.querySelector("[data-floorplan-img]");
    if (img) {
      var fpWrap = img.closest(".floorplans-container");
      if (fpWrap) {
        fpWrap.querySelectorAll("[data-floorplan-sel]").forEach(function (btn) {
          btn.addEventListener("click", function () {
            fpWrap.querySelectorAll(".floorplan-item-wrap").forEach(function (b) {
              b.classList.remove("selected");
            });
            btn.classList.add("selected");
            var media = btn.getAttribute("data-floorplan-media");
            if (media) img.src = media;
            var t = btn.querySelector(".title");
            if (t && t.textContent) img.alt = t.textContent;
          });
        });
      }
    }

    /* ---------- ProjectGallery lightbox ---------- */
    var lightbox = document.querySelector("[data-proj-lightbox]");
    if (lightbox) {
      var slides = Array.prototype.slice.call(lightbox.querySelectorAll(".proj-lightbox-slide"));
      var idx = 0;
      function show(i) {
        if (!slides.length) return;
        idx = ((i % slides.length) + slides.length) % slides.length;
        slides.forEach(function (s, j) { s.classList.toggle("active", j === idx); });
      }
      document.querySelectorAll("[data-proj-open]").forEach(function (el) {
        el.addEventListener("click", function () {
          show(parseInt(el.getAttribute("data-proj-open"), 10));
          lightbox.hidden = false;
        });
      });
      lightbox.addEventListener("click", function () { lightbox.hidden = true; });
      var closeBtn = lightbox.querySelector("[data-proj-close]");
      if (closeBtn) closeBtn.addEventListener("click", function () { lightbox.hidden = true; });
      var prevBtn = lightbox.querySelector("[data-proj-prev]");
      if (prevBtn) prevBtn.addEventListener("click", function (e) { e.stopPropagation(); show(idx - 1); });
      var nextBtn = lightbox.querySelector("[data-proj-next]");
      if (nextBtn) nextBtn.addEventListener("click", function (e) { e.stopPropagation(); show(idx + 1); });
    }
  });
})();