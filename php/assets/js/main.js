/* main.js — site header behavior (port of src/components/header.tsx)
 * Wire-up: mobile drawer, drawer accordions, scroll transparency. */
(function () {
  "use strict";

  var wrap = document.querySelector(".header-wrap");

  if (wrap && wrap.classList.contains("header-transparent")) {
    var onScroll = function () {
      wrap.classList.toggle("header-transparent", window.scrollY <= 10);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  var overlay = document.querySelector(".js-mobile-drawer");
  if (overlay) {
    var openBtns = document.querySelectorAll(".js-mobile-drawer-open");
    var closeBtns = document.querySelectorAll(".js-mobile-drawer-close");

    var openDrawer = function () {
      overlay.hidden = false;
      document.body.style.overflow = "hidden";
    };
    var closeDrawer = function () {
      overlay.hidden = true;
      document.body.style.overflow = "";
    };

    openBtns.forEach(function (b) { b.addEventListener("click", openDrawer); });
    closeBtns.forEach(function (b) { b.addEventListener("click", closeDrawer); });
    overlay.addEventListener("click", function (e) {
      if (e.target === overlay) closeDrawer();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !overlay.hidden) closeDrawer();
    });
  }

  document.querySelectorAll(".js-mobile-accordion").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var expanded = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", String(!expanded));
      btn.classList.toggle("collapsed", expanded);
      var panel = btn.closest(".accordion-item")
        ? btn.closest(".accordion-item").querySelector(".accordion-collapse")
        : null;
      if (panel) panel.hidden = expanded;
    });
  });
})();