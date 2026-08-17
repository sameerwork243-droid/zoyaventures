// content-ui.js — client behaviour ported from search-hero.tsx, blog-listing.tsx,
// area-guides-listing.tsx, developer-listing.tsx, team-listing-client.tsx,
// faq.tsx, office-card.tsx, dream-home-quiz.tsx, contact-enquiry-form.tsx,
// list-property-form.tsx and the slick prev/next arrows.
(function () {
  "use strict";

  function jsonEmbed(sel) {
    var el = document.querySelector(sel);
    if (!el) return null;
    try {
      return JSON.parse(el.textContent || "null");
    } catch (e) {
      return null;
    }
  }

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  /* ---------- FaqList accordion (faq.tsx) ---------- */

  document.querySelectorAll(".faq-list").forEach(function (list) {
    var items = Array.prototype.slice.call(list.querySelectorAll(".accordion-item"));
    items.forEach(function (item, i) {
      var btn = item.querySelector(".accordion-button");
      if (!btn) return;
      btn.addEventListener("click", function () {
        var willOpen = !item.classList.contains("open");
        items.forEach(function (other, j) {
          var ob = other.querySelector(".accordion-button");
          var body = other.querySelector(".accordion-collapse");
          var isTarget = j === i && willOpen;
          if (isTarget) other.classList.add("open");
          else other.classList.remove("open");
          if (ob) {
            if (isTarget) {
              ob.classList.remove("collapsed");
              ob.setAttribute("aria-expanded", "true");
            } else {
              ob.classList.add("collapsed");
              ob.setAttribute("aria-expanded", "false");
            }
          }
          if (body) {
            if (isTarget) body.classList.add("show");
            else body.classList.remove("show");
          }
        });
      });
    });
  });

  /* ---------- custom slider arrows (slick prev/next) ---------- */

  document.querySelectorAll(".custom-slider-arrows").forEach(function (arrows) {
    var slider = arrows.closest(".slick-slider");
    if (!slider) return;
    var track = slider.querySelector(".slick-track");
    var slides = Array.prototype.slice.call(slider.querySelectorAll(".slick-slide"));
    if (!track || !slides.length) return;
    var prev = arrows.querySelector(".button-back");
    var next = arrows.querySelector(".button-next");
    var w = parseFloat((slides[0].style.width || "100").replace("%", ""));
    var perView = w > 0 ? Math.max(1, 100 / w) : 1;
    var maxIdx = Math.max(0, Math.ceil(slides.length - perView));
    var idx = 0;

    track.style.width = slides.length * w + "%";
    track.style.transition = "transform 0.4s ease";

    function update() {
      track.style.transform = "translateX(-" + idx * w + "%)";
      if (prev) {
        prev.disabled = idx === 0;
        prev.classList.toggle("button-disabled", idx === 0);
      }
      if (next) {
        next.disabled = idx >= maxIdx;
        next.classList.toggle("button-disabled", idx >= maxIdx);
      }
    }

    if (prev) prev.addEventListener("click", function () { if (idx > 0) { idx--; update(); } });
    if (next) next.addEventListener("click", function () { if (idx < maxIdx) { idx++; update(); } });
    update();
  });

  /* ---------- HeroSearch (search-hero.tsx) ---------- */

  var heroAreas = jsonEmbed("[data-hero-areas]") || [];

  var BED_OPTIONS = [["", "No Min"], ["0", "Studio"], ["1", "1"], ["2", "2"], ["3", "3"], ["4", "4"], ["5", "5"], ["6", "6"], ["7", "7"], ["8", "8"], ["9", "9"]];
  var BED_MAX_OPTIONS = [["", "No Max"], ["0", "Studio"], ["1", "1"], ["2", "2"], ["3", "3"], ["4", "4"], ["5", "5"], ["6", "6"], ["7", "7"], ["8", "8"], ["9", "9"]];
  var PRICE_VALUES = [300000, 400000, 500000, 600000, 700000, 800000, 900000, 1000000, 1100000, 1200000, 1300000, 1400000, 1500000, 1600000, 1700000, 1800000, 1900000, 2000000, 2100000, 2200000, 2300000, 2400000, 2500000, 2600000, 2700000, 2800000, 2900000, 3000000, 3250000, 3500000, 3750000, 4000000, 4250000, 4500000, 5000000, 6000000, 7000000, 8000000, 9000000, 10000000, 20000000, 25000000, 50000000];
  var AED_TO_USD = 0.27229402;
  function usdLabel(v) {
    return "USD " + Math.floor(v * AED_TO_USD).toLocaleString("en-US");
  }
  function priceOpts() {
    return PRICE_VALUES.map(function (v) { return [String(v), usdLabel(v)]; });
  }
  var HERO_OPTION_LISTS = {
    minBed: BED_OPTIONS,
    maxBed: BED_MAX_OPTIONS,
    minPrice: [["", "No Min"]].concat(priceOpts()),
    maxPrice: [["", "No Max"]].concat(priceOpts()),
  };
  var HERO_OPTION_LABELS = {
    minBed: "Min Bedrooms",
    maxBed: "Max Bedrooms",
    minPrice: "Min Price",
    maxPrice: "Max Price",
  };

  var heroRoot = document.querySelector("[data-hero-search]");
  if (heroRoot) {
    var heroTab = 0;
    var heroQ = "";
    var heroAreaSlug = "";
    var heroVals = { minBed: "", maxBed: "", minPrice: "", maxPrice: "" };

    Object.keys(HERO_OPTION_LISTS).forEach(function (key) {
      var menu = heroRoot.querySelector('[data-hero-options="' + key + '"]');
      if (!menu) return;
      HERO_OPTION_LISTS[key].forEach(function (pair) {
        var div = document.createElement("div");
        div.className = "react-select__option";
        div.setAttribute("role", "option");
        div.setAttribute("data-hero-option", key);
        div.setAttribute("data-value", pair[0]);
        div.textContent = pair[1];
        menu.appendChild(div);
      });
    });

    heroRoot.querySelectorAll("[data-hero-tab]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        heroTab = parseInt(btn.getAttribute("data-hero-tab") || "0", 10) || 0;
        heroRoot.querySelectorAll("[data-hero-tab]").forEach(function (b) {
          b.classList.toggle("selected-tab", b === btn);
        });
      });
    });

    var heroInput = heroRoot.querySelector("[data-hero-input]");
    var suggWrap = heroRoot.querySelector("[data-hero-suggestions]");
    var suggList = suggWrap ? suggWrap.querySelector(".autosuggest__suggestions-list") : null;

    function renderSuggestions() {
      if (!suggWrap || !suggList) return;
      suggList.innerHTML = "";
      var t = heroQ.trim().toLowerCase();
      if (t.length < 2) {
        suggWrap.style.display = "none";
        return;
      }
      var matches = heroAreas.filter(function (a) { return a.toLowerCase().indexOf(t) !== -1; }).slice(0, 6);
      if (!matches.length) {
        suggWrap.style.display = "none";
        return;
      }
      matches.forEach(function (label) {
        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "autosuggest__suggestion";
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M10 7C10 8.10457 9.10457 9 8 9C6.89543 9 6 8.10457 6 7C6 5.89543 6.89543 5 8 5C9.10457 5 10 5.89543 10 7Z" stroke="#9399A4" /><path d="M13 7C13 11.7614 8 14.5 8 14.5C8 14.5 3 11.7614 3 7C3 4.23858 5.23858 2 8 2C10.7614 2 13 4.23858 13 7Z" stroke="#9399A4" /></svg>' + esc(label);
        btn.addEventListener("mousedown", function (e) {
          e.preventDefault();
          heroQ = label;
          heroAreaSlug = label.toLowerCase().replace(/[^a-z0-9]+/g, "-");
          heroInput.value = label;
          suggWrap.style.display = "none";
        });
        suggList.appendChild(btn);
      });
      suggWrap.style.display = "block";
    }

    if (heroInput) {
      heroInput.addEventListener("input", function () {
        heroQ = heroInput.value;
        heroAreaSlug = "";
        renderSuggestions();
      });
      heroInput.addEventListener("focus", renderSuggestions);
      heroInput.addEventListener("blur", function () {
        setTimeout(function () { if (suggWrap) suggWrap.style.display = "none"; }, 150);
      });
      heroInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") heroGo();
      });
    }

    function closeHeroMenus() {
      heroRoot.querySelectorAll(".react-select--is-open").forEach(function (box) {
        box.classList.remove("react-select--is-open");
        var ctl = box.querySelector(".react-select__control");
        if (ctl) ctl.classList.remove("react-select__control--menu-is-open");
        var menu = box.querySelector(".react-select__menu");
        if (menu) menu.style.display = "none";
      });
    }

    document.addEventListener("mousedown", function (e) {
      if (heroRoot && !heroRoot.contains(e.target)) {
        if (suggWrap) suggWrap.style.display = "none";
        closeHeroMenus();
      }
    });

    heroRoot.querySelectorAll("[data-hero-option]").forEach(function (opt) {
      opt.addEventListener("click", function () {
        var key = opt.getAttribute("data-hero-option");
        var val = opt.getAttribute("data-value");
        heroVals[key] = val;
        var wrap = heroRoot.querySelector('[data-hero-select="' + key + '"]');
        if (wrap) {
          var label = wrap.querySelector(".react-select__single-value");
          var sel = HERO_OPTION_LISTS[key].find(function (p) { return p[0] === val; });
          if (label && sel) label.textContent = sel[1];
          closeHeroMenus();
        }
      });
    });

    function heroGo() {
      var tabBase = heroTab === 0 ? "/buy/properties-for-sale" : heroTab === 1 ? "/let/properties-for-rent" : "/new-projects";
      var target = heroAreaSlug ? tabBase + "/in-" + heroAreaSlug + "/" : tabBase + "/";
      var params = [];
      if (heroVals.minBed) params.push("minBedroom=" + encodeURIComponent(heroVals.minBed));
      if (heroVals.maxBed) params.push("maxBedroom=" + encodeURIComponent(heroVals.maxBed));
      if (heroVals.minPrice) params.push("minPrice=" + encodeURIComponent(heroVals.minPrice));
      if (heroVals.maxPrice) params.push("maxPrice=" + encodeURIComponent(heroVals.maxPrice));
      if (heroQ.trim() && !heroAreaSlug) params.push("areas=" + encodeURIComponent(heroQ.trim()));
      window.location.href = target + (params.length ? "?" + params.join("&") : "");
    }

    var heroGoBtn = heroRoot.querySelector("[data-hero-go]");
    if (heroGoBtn) heroGoBtn.addEventListener("click", heroGo);
  }

  /* ---------- BlogListing (blog-listing.tsx) ---------- */

  var blogRoot = document.querySelector("[data-blog-listing]");
  if (blogRoot) {
    var blogPosts = jsonEmbed("[data-blog-json]") || [];
    var blogPerPage = 12;
    var blogState = { q: "", cat: "All Categories", page: 1, pageOpen: false };

    function blogCategories() {
      var seen = [];
      blogPosts.forEach(function (p) {
        if (p.category && seen.indexOf(p.category) === -1) seen.push(p.category);
      });
      return ["All Categories"].concat(seen);
    }

    function blogFiltered() {
      var q = blogState.q.trim().toLowerCase();
      return blogPosts.filter(function (p) {
        if (blogState.cat !== "All Categories" && p.category !== blogState.cat) return false;
        if (q && (p.title + " " + p.category).toLowerCase().indexOf(q) === -1) return false;
        return true;
      });
    }

    function blogRender() {
      var filtered = blogFiltered();
      var totalPages = Math.max(1, Math.ceil(filtered.length / blogPerPage));
      var safePage = Math.min(blogState.page, totalPages);
      var items = filtered.slice((safePage - 1) * blogPerPage, safePage * blogPerPage);
      var list = blogRoot.querySelector("[data-blog-list]");
      if (list) {
        list.innerHTML = "";
        items.forEach(function (b) {
          var href = "/blog/" + encodeURIComponent(b.slug) + "/";
          var html = '<div class="news-card-wrapper"><div class="news-card">'
            + '<div class="img-section-wrap img-zoom"><a class="img-section" href="' + esc(href) + '">';
          if (b.image) html += '<img loading="lazy" src="' + esc(b.image) + '" alt="' + esc(b.title) + '" />';
          if (b.category) html += '<p class="img-tag">' + esc(b.category) + '</p>';
          html += '</a></div>'
            + '<a class="title" href="' + esc(href) + '">' + esc(b.title) + '</a>'
            + '<p class="date">' + esc(b.date) + '</p>'
            + '</div></div>';
          list.insertAdjacentHTML("beforeend", html);
        });
      }
      var value = blogRoot.querySelector("[data-blog-category-value]");
      if (value) value.textContent = blogState.cat;
      blogRoot.querySelectorAll("[data-blog-category]").forEach(function (opt) {
        var sel = opt.getAttribute("data-blog-category") === blogState.cat;
        opt.classList.toggle("react-select__option--is-selected", sel);
      });

      var nav = blogRoot.querySelector(".pagination-wrapper");
      if (nav) {
        nav.parentNode.removeChild(nav);
        if (totalPages > 1) {
          var n = blogNav(totalPages, safePage);
          blogRoot.querySelector(".blog-listing-container").insertAdjacentHTML("beforeend", n);
        }
      }
    }

    function blogNav(totalPages, safePage) {
      var opts = "";
      for (var n = 1; n <= totalPages; n++) {
        opts += '<div class="pagination-select__option' + (n === safePage ? " pagination-select__option--is-selected" : "") + '" data-blog-page="' + n + '">' + n + "</div>";
      }
      return '<nav class="pagination-wrapper"><div><div class="pagination-container">'
        + '<button class="button button-white pagination-button button-back' + (safePage === 1 ? " button-disabled" : "") + '" data-blog-back' + (safePage === 1 ? " disabled" : "") + ' type="button">'
        + '<svg class="arrow-left-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        + '<span>Back</span></button>'
        + '<div class="pagination-select-wrap"><span class="page-text">Page:</span>'
        + '<div class="pagination-select"><div class="react-select css-b62m3t-container">'
        + '<div class="pagination-select__control css-13cymwt-control" data-blog-page-toggle>'
        + '<div class="pagination-select__value-container pagination-select__value-container--has-value css-hlgwow">'
        + '<div class="pagination-select__single-value css-1dimb5e-singleValue" data-blog-page-value>' + safePage + '</div></div>'
        + '<div class="pagination-select__indicators css-1wy0on6"><span class="pagination-select__indicator-separator css-1uei4ir-indicatorSeparator"></span>'
        + '<div class="dropdown-indicator react-select__indicator react-select__dropdown-indicator css-15ctyzv-indicatorContainer" aria-hidden="true">'
        + '<svg class="arrow-down-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 5.5L8 10.5L3 5.5" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        + '</div></div></div>'
        + '<div class="pagination-select__menu"><div class="pagination-select__menu-list">' + opts + '</div></div></div></div>'
        + '<span class="page-text">of ' + totalPages + '</span></div>'
        + '<button class="button button-white pagination-button button-next' + (safePage >= totalPages ? " button-disabled" : "") + '" data-blog-next' + (safePage >= totalPages ? " disabled" : "") + ' type="button">'
        + '<span>Next</span>'
        + '<svg class="arrow-right-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M8.25 4.5L15.75 12L8.25 19.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        + '</button></div></div></nav>';
    }

    var blogSearch = blogRoot.querySelector("[data-blog-search]");
    if (blogSearch) {
      blogSearch.addEventListener("input", function () {
        blogState.q = blogSearch.value;
        blogState.page = 1;
        blogRender();
      });
    }
    blogRoot.querySelectorAll("[data-blog-category]").forEach(function (opt) {
      opt.addEventListener("click", function () {
        blogState.cat = opt.getAttribute("data-blog-category");
        blogState.page = 1;
        blogRender();
      });
    });
    document.addEventListener("click", function (e) {
      var target = e.target;
      var back = target.closest ? target.closest("[data-blog-back]") : null;
      var next = target.closest ? target.closest("[data-blog-next]") : null;
      var pg = target.closest ? target.closest("[data-blog-page]") : null;
      var toggle = target.closest ? target.closest("[data-blog-page-toggle]") : null;
      if (back && !back.disabled) {
        var fb = blogFiltered();
        var tp = Math.max(1, Math.ceil(fb.length / blogPerPage));
        blogState.page = Math.max(1, Math.min(tp, blogState.page - 1));
        blogRender();
      } else if (next && !next.disabled) {
        var fn = blogFiltered();
        var tn = Math.max(1, Math.ceil(fn.length / blogPerPage));
        blogState.page = Math.max(1, Math.min(tn, blogState.page + 1));
        blogRender();
      } else if (pg) {
        blogState.page = parseInt(pg.getAttribute("data-blog-page") || "1", 10) || 1;
        blogRender();
      } else if (toggle) {
        var box = toggle.closest(".pagination-select .react-select");
        var menu = box ? box.querySelector(".pagination-select__menu") : null;
        if (box && menu) {
          var wasOpen = box.classList.contains("react-select--is-open");
          if (wasOpen) {
            box.classList.remove("react-select--is-open");
            toggle.classList.remove("react-select__control--menu-is-open");
            menu.style.display = "none";
          } else {
            box.classList.add("react-select--is-open");
            toggle.classList.add("react-select__control--menu-is-open");
            menu.style.display = "block";
          }
        }
      }
    });
    blogRender();
  }

  /* ---------- AreaGuidesListing (area-guides-listing.tsx) ---------- */

  var areaRoot = document.querySelector("[data-area-guides]");
  if (areaRoot) {
    var areaGuides = jsonEmbed("[data-area-json]") || [];
    var areaState = { q: "", amenity: "All" };

    function areaFiltered() {
      var t = areaState.q.trim().toLowerCase();
      return areaGuides.filter(function (a) {
        if (t && a.title.toLowerCase().indexOf(t) === -1) return false;
        if (areaState.amenity !== "All") {
          var has = (a.amenities || []).some(function (x) { return String(x).toLowerCase() === areaState.amenity.toLowerCase(); });
          if (!has) return false;
        }
        return true;
      });
    }

    function areaRender() {
      var matches = areaFiltered();
      var visible = areaState.q.trim() || areaState.amenity !== "All" ? matches : matches.slice(0, 24);
      var list = areaRoot.querySelector("[data-area-list]");
      if (!list) return;
      list.innerHTML = "";
      visible.forEach(function (a) {
        var href = "/area-guides/" + encodeURIComponent(a.slug) + "/";
        var html = '<div class="areaguide-card" data-area-card="' + esc(a.slug) + '">'
          + '<div class="img-section img-zoom"><a class="tt-fi" href="' + esc(href) + '">';
        if (a.image) {
          html += '<img loading="lazy" draggable="false" src="' + esc(a.image) + '" srcSet="' + esc(a.image) + ' 340w, ' + esc(a.image304 || a.image) + ' 304w" sizes="(min-width: 100px) 340px" alt="' + esc(a.title) + '" />';
        }
        html += '</a></div>'
          + '<a class="title" href="' + esc(href) + '">' + esc(a.title) + '</a>';
        if (a.desc) html += '<a class="description" href="' + esc(href) + '">' + a.desc + '</a>';
        html += '</div>';
        list.insertAdjacentHTML("beforeend", html);
      });
      var value = areaRoot.querySelector("[data-area-amenity-value]");
      if (value) value.textContent = areaState.amenity;
      areaRoot.querySelectorAll("[data-area-amenity]").forEach(function (opt) {
        var sel = opt.getAttribute("data-area-amenity") === areaState.amenity;
        opt.classList.toggle("react-select__option--is-selected", sel);
      });
    }

    var areaSearch = areaRoot.querySelector("[data-area-search]");
    if (areaSearch) {
      areaSearch.addEventListener("input", function () {
        areaState.q = areaSearch.value;
        areaRender();
      });
    }
    areaRoot.querySelectorAll("[data-area-amenity]").forEach(function (opt) {
      opt.addEventListener("click", function () {
        areaState.amenity = opt.getAttribute("data-area-amenity");
        areaRender();
        var box = areaRoot.querySelector(".react-select");
        if (box) box.classList.remove("react-select--is-open");
        var ctl = areaRoot.querySelector(".react-select__control");
        if (ctl) ctl.classList.remove("react-select__control--menu-is-open");
        var menu = areaRoot.querySelector(".react-select__menu");
        if (menu) menu.style.display = "none";
      });
    });
    areaRender();
  }

  /* ---------- DeveloperListing (developer-listing.tsx) ---------- */

  var devRoot = document.querySelector("[data-developer-listing]");
  if (devRoot) {
    var devCards = Array.prototype.slice.call(devRoot.querySelectorAll("[data-developer-card]")).map(function (card) {
      var nameEl = card.querySelector(".name");
      return { card: card, name: (nameEl ? nameEl.textContent : "").trim() };
    });
    var devSearch = devRoot.querySelector("[data-developer-search]");
    if (devSearch) {
      devSearch.addEventListener("input", function () {
        var q = devSearch.value.trim().toLowerCase();
        devCards.forEach(function (d) {
          d.card.style.display = !q || d.name.toLowerCase().indexOf(q) !== -1 ? "" : "none";
        });
      });
    }
  }

  /* ---------- TeamListing (team-listing-client.tsx) ---------- */

  var teamRoot = document.querySelector("[data-team-list]");
  if (teamRoot) {
    var teamMembers = jsonEmbed("[data-team-json]") || [];
    var TEAM_TABS = ["Management", "Associates", "Sales Managers", "Managers", "Primary Brokers", "Secondary Brokers"];
    var TAB_MAP = {
      Management: "Management",
      Associates: "Associate",
      "Sales Managers": "Manager - Sales",
      Managers: "Manager",
      "Primary Brokers": "Primary Brokers",
      "Secondary Brokers": "Secondary Brokers",
    };
    var TEAM_PER_PAGE = 20;
    var teamState = { tab: "Management", category: "", language: "", q: "", page: 1 };

    var teamBack = document.querySelector("[data-team-back]");
    var teamNext = document.querySelector("[data-team-next]");
    var teamPage = document.querySelector("[data-team-page]");

    function teamFiltered() {
      var cat = teamState.category || (TAB_MAP[teamState.tab] || "");
      var q = teamState.q.trim().toLowerCase();
      return teamMembers.filter(function (m) {
        if (cat && (m.category || []).indexOf(cat) === -1) return false;
        if (teamState.language && (m.languages || []).indexOf(teamState.language) === -1) return false;
        if (q) {
          var name = (m.name || "").toLowerCase();
          var desig = (m.designation || "").toLowerCase();
          if (name.indexOf(q) === -1 && desig.indexOf(q) === -1) return false;
        }
        return true;
      });
    }

    function teamRender() {
      var filtered = teamFiltered();
      var totalPages = Math.max(1, Math.ceil(filtered.length / TEAM_PER_PAGE));
      var current = Math.min(teamState.page, totalPages);
      var pageItems = filtered.slice((current - 1) * TEAM_PER_PAGE, current * TEAM_PER_PAGE);
      var inner = teamRoot.querySelector(".team-listing-section-inner");
      if (inner) {
        inner.innerHTML = "";
        pageItems.forEach(function (m) {
          var href = "/team/" + encodeURIComponent(m.slug) + "/";
          var html = '<div class="team-card-wrap"><div class="team-card rounded-card">'
            + '<a class="img-section img-zoom" href="' + esc(href) + '">';
          if (m.image) html += '<img loading="lazy" draggable="false" src="' + esc(m.image) + '" alt="' + esc(m.name) + '" />';
          html += '</a>'
            + '<a href="' + esc(href) + '"><p class="name">' + esc(m.name) + '</p></a>'
            + '<p class="designation">' + esc(m.designation) + '</p>'
            + '</div></div>';
          inner.insertAdjacentHTML("beforeend", html);
        });
      }
      if (teamPage) teamPage.textContent = current + " of " + totalPages;
      if (teamBack) {
        teamBack.disabled = current === 1;
        teamBack.classList.toggle("button-disabled", current === 1);
      }
      if (teamNext) {
        teamNext.disabled = current >= totalPages;
        teamNext.classList.toggle("button-disabled", current >= totalPages);
      }
    }

    function teamSelectTab(t) {
      teamState.tab = t;
      teamState.category = "";
      teamState.page = 1;
      document.querySelectorAll("[data-team-tab]").forEach(function (b) {
        var active = b.getAttribute("data-team-tab") === t;
        b.classList.toggle("selected-tab", active);
        b.classList.toggle("button-white", !active);
      });
      var catSel = document.querySelector("[data-team-category]");
      if (catSel) {
        var first = catSel.querySelector("option");
        if (first) first.textContent = t;
        catSel.value = "";
      }
      teamRender();
    }

    document.querySelectorAll("[data-team-tab]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        teamSelectTab(btn.getAttribute("data-team-tab") || "Management");
      });
    });
    var teamSearch = document.querySelector("[data-team-search]");
    if (teamSearch) {
      teamSearch.addEventListener("input", function () {
        teamState.q = teamSearch.value;
        teamState.page = 1;
        teamRender();
      });
    }
    var teamCatSel = document.querySelector("[data-team-category]");
    if (teamCatSel) {
      teamCatSel.addEventListener("change", function () {
        teamState.category = teamCatSel.value;
        teamState.page = 1;
        teamRender();
      });
    }
    var teamLangSel = document.querySelector("[data-team-language]");
    if (teamLangSel) {
      teamLangSel.addEventListener("change", function () {
        teamState.language = teamLangSel.value;
        teamState.page = 1;
        teamRender();
      });
    }
    if (teamBack) teamBack.addEventListener("click", function () { if (!teamBack.disabled) { teamState.page = Math.max(1, teamState.page - 1); teamRender(); } });
    if (teamNext) teamNext.addEventListener("click", function () { if (!teamNext.disabled) { teamState.page += 1; teamRender(); } });
    teamRender();
  }

  /* ---------- forms (contact-enquiry-form.tsx + list-property-form.tsx) ---------- */

  document.addEventListener("submit", function (e) {
    var form = e.target;
    if (!form.matches || !form.matches('[data-enquiry-form="contact"], [data-enquiry-form="listing"]')) return;
    e.preventDefault();
    var kind = form.getAttribute("data-enquiry-form");
    var nameEl = form.querySelector('input[name="name"]');
    var emailEl = form.querySelector('input[name="email"]');
    var phoneEl = form.querySelector('input[name="phone"]');
    var dialEl = form.querySelector(".country-select");
    var msgEl = form.querySelector('textarea[name="message"]');
    var okEl = form.querySelector(".success-msg");
    var errEl = form.querySelector(".error-msg");
    var submitBtn = form.querySelector('button[type="submit"]');
    var busy = false;

    function setErr(msg) {
      if (errEl) {
        errEl.style.display = "";
        errEl.innerHTML = esc(msg);
      }
      if (okEl) okEl.style.display = "none";
    }

    function val(el) {
      return el ? (el.value || "").trim() : "";
    }

    if (kind === "listing") {
      var txEl = form.querySelector('select[name="transaction"]');
      var typeEl = form.querySelector('select[name="property_type"]');
      var areaEl = form.querySelector('input[name="community"]');
      var langEl = form.querySelector('select[name="preferred_language"]');
      var bedsEl = form.querySelector('select[name="bedrooms"]');
      var bathsEl = form.querySelector('select[name="bathrooms"]');
      var sizeEl = form.querySelector('input[name="size_sqft"]');
      var priceEl = form.querySelector('input[name="expected_price"]');
      var ownEl = form.querySelector('select[name="ownership"]');
      var addrEl = form.querySelector('input[name="property_address"]');
      var consentEl = form.querySelector('input[name="consent"]');
      if (!val(nameEl)) { setErr("Please enter your full name."); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val(emailEl))) { setErr("Please enter a valid email address."); return; }
      if (!/^[0-9\s\-+()]{7,16}$/.test(phoneEl ? (phoneEl.value || "").trim() : "")) { setErr("Please enter a valid phone number."); return; }
      if (!val(txEl)) { setErr("Please select what you would like to do."); return; }
      if (!val(typeEl)) { setErr("Please select a property type."); return; }
      if (!val(areaEl)) { setErr("Please enter the community or area."); return; }
      if (!val(langEl)) { setErr("Please select your preferred language."); return; }
      if (!val(bedsEl)) { setErr("Please select the number of bedrooms."); return; }
      if (!val(bathsEl)) { setErr("Please select the number of bathrooms."); return; }
      if (!val(sizeEl) || Number.isNaN(Number(sizeEl.value))) { setErr("Please enter the property size in sq ft."); return; }
      if (!val(priceEl) || Number.isNaN(Number(priceEl.value))) { setErr("Please enter the expected price."); return; }
      if (!val(ownEl)) { setErr("Please select the ownership status."); return; }
      if (!val(addrEl)) { setErr("Please enter the property address."); return; }
      if (consentEl && !consentEl.checked) { setErr("Please tick the consent box to continue."); return; }
    }
    if (busy) return;
    busy = true;
    submitBtn.disabled = true;
    submitBtn.querySelector("span").textContent = "Submitting…";

    var payload;
    if (kind === "listing") {
      payload = {
        kind: "listing",
        name: val(nameEl),
        email: val(emailEl),
        phone: ((dialEl ? dialEl.value : "+971") + " " + val(phoneEl)).trim(),
        property_slug: "List Your Property",
        message: JSON.stringify({
          transaction: txEl ? txEl.value : "",
          property_type: typeEl ? typeEl.value : "",
          community: val(areaEl),
          preferred_language: langEl ? langEl.value : "",
          bedrooms: bedsEl ? bedsEl.value : "",
          bathrooms: bathsEl ? bathsEl.value : "",
          size_sqft: val(sizeEl),
          expected_price: val(priceEl),
          ownership: val(ownEl),
          property_address: val(addrEl),
          message: val(msgEl),
        }),
      };
    } else {
      payload = {
        kind: "contact",
        name: val(nameEl),
        email: val(emailEl),
        phone: ((dialEl ? dialEl.value : "+971") + " " + val(phoneEl)).trim(),
        message: val(msgEl),
      };
    }

    fetch("/api/inquiries", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then(function (res) {
        return res.json().catch(function () { return null; }).then(function (d) {
          if (!res.ok) {
            setErr((d && d.error) || "Something went wrong. Please try again.");
            return;
          }
          if (errEl) errEl.style.display = "none";
          if (okEl) okEl.style.display = "";
          form.querySelectorAll('input[name="name"], input[name="email"], input[name="phone"], textarea[name="message"]').forEach(function (el) { el.value = ""; });
          if (kind === "listing") {
            form.querySelectorAll('input[name="community"], input[name="size_sqft"], input[name="expected_price"], input[name="property_address"]').forEach(function (el) { el.value = ""; });
            var own = form.querySelector('select[name="ownership"]');
            if (own) own.value = "";
            var consent = form.querySelector('input[name="consent"]');
            if (consent) consent.checked = false;
          }
        });
      })
      .catch(function () {
        setErr("Something went wrong. Please try again.");
      })
      .finally(function () {
        busy = false;
        submitBtn.disabled = false;
        submitBtn.querySelector("span").textContent = kind === "listing" ? "Submit Details" : "Submit";
      });
  });

  /* ---------- OfficeCard modal (office-card.tsx) ---------- */

  document.addEventListener("click", function (e) {
    var trig = e.target && e.target.closest ? e.target.closest("[data-office-trigger]") : null;
    if (!trig) return;
    var item = trig.closest(".office-item");
    if (!item) return;
    var title = item.getAttribute("data-title") || "";
    var address = item.getAttribute("data-address") || "";
    var phone = item.getAttribute("data-phone") || "";
    var phoneTel = item.getAttribute("data-phone-tel") || "";
    var maps = item.getAttribute("data-maps") || "";
    var href = item.getAttribute("data-href") || "/contact/";
    var modal = document.createElement("div");
    modal.className = "modal fade show d-block";
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-modal", "true");
    var desc = "";
    if (maps) desc += '<a href="' + esc(maps) + '" target="_blank" rel="noreferrer" class="maps-link"><span>View on Google Maps</span></a>';
    if (phone) desc += '<p class="phone"><a href="tel:' + esc(phoneTel) + '">' + esc(phone) + "</a></p>";
    modal.innerHTML = '<div class="modal-dialog modal-fullscreen-md"><div class="modal-content">'
      + '<div class="modal-header"><button class="modal-close" type="button" aria-label="Close">'
      + '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 5L15 15M15 5L5 15" stroke="#07234B" strokeWidth="1.5" strokeLinecap="round" /></svg>'
      + "</button></div>"
      + '<div class="modal-body"><div class="office-contact-modal-header"><div class="content-section">'
      + '<p class="title">' + esc(title) + "</p>"
      + '<p class="address">' + esc(address) + "</p>"
      + '<div class="description">' + desc + "</div>"
      + "</div></div>"
      + '<a class="button button-orange" href="' + esc(href) + '"><span>Contact Office</span></a>'
      + "</div></div></div>";
    document.body.appendChild(modal);
    var prevOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    function close() {
      if (modal.parentNode) modal.parentNode.removeChild(modal);
      document.body.style.overflow = prevOverflow;
    }
    var closeBtn = modal.querySelector(".modal-close");
    if (closeBtn) closeBtn.addEventListener("click", close);
  });

  /* ---------- DreamHomeQuiz modal (dream-home-quiz.tsx) ---------- */

  var QUIZ = jsonEmbed("[data-quiz-json]") || null;
  var quizOpen = false;
  var quizModal = null;

  function quizIcon(name) {
    if (name === "phone") {
      return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" /></svg>';
    }
    if (name === "email") {
      return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" /></svg>';
    }
    if (name === "whatsapp") {
      return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.353.097 11.933c0 2.096.548 4.142 1.588 5.946L0 24l6.305-1.651a11.91 11.91 0 0 0 5.696 1.449h.005c6.582 0 11.94-5.353 11.948-11.934.002-3.188-1.244-6.185-3.434-8.415" /></svg>';
    }
    return "";
  }

  function quizOpenModal() {
    if (!QUIZ || quizOpen) return;
    quizOpen = true;
    var state = {
      step: 0,
      answers: {},
      name: "", email: "", phone: "", country: "AE", lang: "",
      errors: {}, busy: false, submitted: false, fail: "",
    };
    var questions = QUIZ.questions || [];
    var countries = QUIZ.countries || [];
    var languages = QUIZ.languages || [];
    var body = document.body;
    var prevOverflow = body.style.overflow;
    body.style.overflow = "hidden";

    function qkey() { return questions[state.step] ? questions[state.step].key : null; }
    function answered() {
      return state.step >= questions.length || !questions[state.step] || !!state.answers[qkey()];
    }

    function render() {
      var isDetails = state.step >= questions.length;
      var q = questions[state.step];
      var pct = ((state.step + (isDetails ? 1 : 0)) / (questions.length + 1)) * 100;
      var head = isDetails
        ? '<div class="dhq-title">Personal Details</div><div class="dhq-subtitle">Tell us how we can reach you</div>'
        : '<div class="dhq-title">Question ' + (state.step + 1) + " of " + questions.length + '</div><div class="dhq-subtitle">' + esc(q.heading) + "</div>";

      var bodyHtml;
      if (!isDetails) {
        var opts = "";
        (q.options || []).forEach(function (o) {
          var sel = state.answers[q.key] === o.id ? " dhq-selected" : "";
          var img = o.img ? '<span class="dhq-option-img"><img src="' + esc(o.img) + '" alt="' + esc(o.label) + '" loading="lazy" /></span>' : "";
          var icon = o.icon ? '<span class="dhq-option-icon">' + quizIcon(o.icon) + "</span>" : "";
          opts += '<button type="button" class="dhq-option' + sel + '" data-dhq-pick="' + esc(o.id) + '">'
            + img
            + '<span class="dhq-option-main">' + icon + '<span class="dhq-option-label">' + esc(o.label) + "</span>"
            + '<span class="dhq-radio"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6 9 17l-5-5" /></svg></span>'
            + "</span></button>";
        });
        bodyHtml = '<div class="dhq-options' + (q.rows === 2 ? " dhq-rows-2" : "") + '">' + opts + "</div>";
      } else {
        var countryOpts = "";
        countries.forEach(function (c) {
          countryOpts += '<option value="' + esc(c.code) + '"' + (state.country === c.code ? " selected" : "") + ">" + esc(c.name) + " (" + esc(c.dial) + ")</option>";
        });
        var langOpts = '<option value="">Select language…</option>';
        languages.forEach(function (l) {
          langOpts += '<option value="' + esc(l) + '"' + (state.lang === l ? " selected" : "") + ">" + esc(l) + "</option>";
        });
        var cur = countries.find(function (c) { return c.code === state.country; }) || { dial: "" };
        bodyHtml = '<form class="dhq-details" novalidate>'
          + '<div class="dhq-field"><label>Name</label><input type="text" placeholder="Your full name" name="name" value="' + esc(state.name) + '" />'
          + (state.errors.name ? '<span class="dhq-error">' + esc(state.errors.name) + "</span>" : "") + "</div>"
          + '<div class="dhq-field"><label>Email</label><input type="email" placeholder="you@example.com" name="email" value="' + esc(state.email) + '" />'
          + (state.errors.email ? '<span class="dhq-error">' + esc(state.errors.email) + "</span>" : "") + "</div>"
          + '<div class="dhq-field"><label>Phone</label>'
          + '<div class="dhq-phone-row"><select name="country">' + countryOpts + "</select>"
          + '<input type="tel" placeholder="Phone number" name="phone" value="' + esc(state.phone) + '" /></div>'
          + '<span class="dhq-code-hint">+' + esc(cur.dial) + "</span>"
          + (state.errors.phone ? '<span class="dhq-error">' + esc(state.errors.phone) + "</span>" : "") + "</div>"
          + '<div class="dhq-field"><label>Preferred Language</label><select name="lang">' + langOpts + "</select></div>"
          + (state.fail ? '<div class="dhq-fail">' + esc(state.fail) + "</div>" : "")
          + '<button type="submit" class="dhq-submit">' + (state.busy ? "Submitting…" : "Submit Details") + "</button>"
          + '<p class="dhq-terms">By submitting you agree to our <a href="/privacy-policy">Privacy Policy</a> and consent to being contacted about your property preferences.</p>'
          + "</form>";
      }

      var foot = "";
      if (state.submitted) {
        foot = "";
      } else if (!isDetails) {
        foot = '<div class="dhq-foot">'
          + (state.step > 0 ? '<button type="button" class="dhq-back" data-dhq-back><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="m15 18-6-6 6-6" /></svg>Back</button>' : "")
          + '<button type="button" class="dhq-next' + (answered() ? "" : " dhq-disabled") + '" data-dhq-next>Next<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="m9 18 6-6-6-6" /></svg></button>'
          + "</div>";
      }

      var main = state.submitted
        ? '<div class="dhq-success"><div class="dhq-success-icon"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6 9 17l-5-5" /></svg></div><h3>Thank you, ' + esc((state.name || "").split(" ")[0]) + '!</h3><p>Your details have been received. A member of our team will contact you shortly.</p><button type="button" class="dhq-submit" data-dhq-close>Close</button></div>'
        : '<div class="dhq-body">' + bodyHtml + "</div>" + foot;

      quizModal.innerHTML = '<div class="dhq-overlay" data-dhq-overlay>'
        + '<div class="dhq-modal" role="dialog" aria-modal="true">'
        + '<div class="dhq-head">' + head
        + '<button type="button" class="dhq-close" aria-label="Close quiz" data-dhq-close><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M18 6 6 18M6 6l12 12" /></svg></button>'
        + '<div class="dhq-progress"><div class="dhq-progress-fill" style="width:' + pct + '%"></div></div>'
        + "</div>"
        + main
        + "</div></div>";
    }

    function close() {
      if (!quizModal || !quizModal.parentNode) return;
      quizModal.parentNode.removeChild(quizModal);
      quizModal = null;
      quizOpen = false;
      body.style.overflow = prevOverflow;
      document.removeEventListener("keydown", quizOnKey);
    }

    function quizOnKey(e) {
      if (e.key === "Escape") close();
    }

    quizModal = document.createElement("div");
    quizModal.className = "dhq-root";
    document.body.appendChild(quizModal);
    render();
    document.addEventListener("keydown", quizOnKey);

    quizModal.addEventListener("click", function (e) {
      var target = e.target;
      if (target.closest("[data-dhq-overlay]") === quizModal.querySelector(".dhq-overlay") && !target.closest(".dhq-modal")) {
        close();
        return;
      }
      var closeBtn = target.closest("[data-dhq-close]");
      if (closeBtn) { close(); return; }
      var pick = target.closest("[data-dhq-pick]");
      if (pick) {
        var key = qkey();
        var id = pick.getAttribute("data-dhq-pick");
        if (key && state.answers[key] === id) delete state.answers[key];
        else if (key) state.answers[key] = id;
        render();
        return;
      }
      var next = target.closest("[data-dhq-next]");
      if (next && !next.classList.contains("dhq-disabled")) {
        state.step += 1;
        render();
        return;
      }
      var back = target.closest("[data-dhq-back]");
      if (back) {
        if (state.step > 0) state.step -= 1;
        render();
        return;
      }
    });

    quizModal.addEventListener("submit", function (e) {
      var form = e.target;
      if (!form.matches(".dhq-details")) return;
      e.preventDefault();
      var fd = new FormData(form);
      state.name = (fd.get("name") || "").trim();
      state.email = (fd.get("email") || "").trim();
      state.phone = (fd.get("phone") || "").trim();
      state.country = fd.get("country") || "AE";
      state.lang = fd.get("lang") || "";
      state.errors = {};
      if (state.name.length < 2) state.errors.name = "Please enter your name";
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.email)) state.errors.email = "Please enter a valid email";
      if (state.phone.replace(/\D/g, "").length < 6) state.errors.phone = "Please enter a valid phone number";
      if (Object.keys(state.errors).length) {
        render();
        return;
      }
      state.busy = true;
      state.fail = "";
      render();
      var cur = countries.find(function (c) { return c.code === state.country; }) || { dial: "" };
      var a = state.answers;
      var message = [
        "Property type: " + (a.propertyType || "-"),
        "Area: " + (a.area || "-"),
        "Budget: " + (a.budget || "-"),
        "Bedrooms: " + (a.bedrooms || "-"),
        "Buyer type: " + (a.buyerType || "-"),
        "Timeline: " + (a.purchaseTimeline || "-"),
        "Communication: " + (a.communicationMethod || "-"),
        "Preferred time: " + (a.preferredContactTime || "-"),
        "Preferred language: " + (state.lang || "-"),
      ].join("\n");
      fetch("/api/inquiries", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          kind: "quiz",
          name: state.name,
          email: state.email,
          phone: (cur.dial + " " + state.phone).trim(),
          message: message,
        }),
      })
        .then(function (res) {
          return res.json().catch(function () { return null; }).then(function (d) {
            if (!res.ok) throw new Error((d && d.error) || "Submission failed");
            state.submitted = true;
            render();
          });
        })
        .catch(function (err) {
          state.fail = (err && err.message) || "Submission failed. Please try again.";
          render();
        })
        .finally(function () {
          state.busy = false;
        });
    });
  }

  document.addEventListener("click", function (e) {
    var trig = e.target && e.target.closest ? e.target.closest("[data-dream-home-quiz-open], [data-quiz-trigger]") : null;
    if (trig) quizOpenModal();
  });
})();