// property.js — client behaviour ported from property-gallery.tsx, property-enquiry-form.tsx,
// save-button.tsx (button variant) and the mortgage-link scroll.
(function () {
  "use strict";

  /* ---------- PropertyGallery lightbox ---------- */

  function initGallery() {
    var root = document.querySelector(".pe-gallery");
    if (!root) return;
    var lightbox = root.querySelector("[data-gallery-lightbox]");
    var mainImg = root.querySelector(".pe-gallery-main img");
    var thumbs = Array.prototype.slice.call(root.querySelectorAll("[data-gallery-thumb]"));
    var prev = root.querySelector("[data-gallery-prev]");
    var next = root.querySelector("[data-gallery-next]");
    var closeBtn = root.querySelector("[data-gallery-close]");
    var countEl = root.querySelector(".pe-lightbox-count");
    var shareMsg = root.querySelector(".pe-gallery-share-msg");
    var shareBtn = root.querySelector("[data-gallery-share]");
    if (!lightbox) return;

    var lightImg = lightbox.querySelector(".pe-lightbox-img img");
    var srcs = [];
    var raw = root.getAttribute("data-srcs");
    if (raw) {
      try { srcs = JSON.parse(raw); } catch (e) {}
    }
    if (!srcs.length) {
      var main = root.querySelector(".pe-gallery-main img");
      if (main) srcs.push(main.getAttribute("src"));
      Array.prototype.forEach.call(root.querySelectorAll(".pe-gallery-side-item img"), function (im) {
        srcs.push(im.getAttribute("src"));
      });
    }
    var openers = Array.prototype.slice.call(root.querySelectorAll("[data-gallery-open]"));
    var n = srcs.length;
    if (!n) return;
    var idx = 0;

    function show() {
      if (!lightImg || !srcs.length) return;
      var cur = ((idx % n) + n) % n;
      lightImg.src = srcs[cur];
      if (countEl) countEl.textContent = (cur + 1) + " / " + n;
      thumbs.forEach(function (t, i) {
        if (t.classList) t.classList.toggle("active", i === cur);
      });
    }

    function open(i) {
      idx = i;
      show();
      lightbox.style.display = "";
      document.body.style.overflow = "hidden";
    }
    function close() {
      lightbox.style.display = "none";
      document.body.style.overflow = "";
    }
    function step(d) {
      idx = idx + d;
      show();
    }

    openers.forEach(function (el) {
      el.addEventListener("click", function () {
        open(parseInt(el.getAttribute("data-gallery-open") || "0", 10));
      });
    });
    if (closeBtn) closeBtn.addEventListener("click", close);
    lightbox.addEventListener("click", function (e) {
      if (e.target === lightbox) close();
    });
    if (prev) prev.addEventListener("click", function () { step(-1); });
    if (next) next.addEventListener("click", function () { step(1); });
    thumbs.forEach(function (t) {
      t.addEventListener("click", function () { open(parseInt(t.getAttribute("data-gallery-thumb") || "0", 10)); });
    });
    document.addEventListener("keydown", function (e) {
      if (lightbox.style.display === "none") return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowRight") step(1);
      if (e.key === "ArrowLeft") step(-1);
    });

    if (shareBtn) {
      shareBtn.addEventListener("click", function () {
        var data = { title: shareBtn.getAttribute("data-share-title") || document.title, url: window.location.href };
        var done = function () {
          if (shareMsg) shareMsg.style.display = "";
          setTimeout(function () { if (shareMsg) shareMsg.style.display = "none"; }, 2500);
        };
        if (navigator.share) {
          navigator.share(data).catch(function () {});
        } else if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(data.url).then(done).catch(function () {});
        }
      });
    }
  }
  initGallery();

  /* ---------- mortgage-link scroll ---------- */

  document.addEventListener("click", function (e) {
    var btn = e.target.closest ? e.target.closest(".mortgage-link") : null;
    if (btn) {
      e.preventDefault();
      var target = document.getElementById("mortgage-calculator");
      if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });

  /* ---------- PropertyEnquiryForm ---------- */

  document.addEventListener("submit", function (e) {
    var form = e.target;
    if (!form.matches || !form.matches("[data-enquiry-form]")) return;
    e.preventDefault();
    var nameEl = form.querySelector("#bav-name");
    var emailEl = form.querySelector("#bav-email");
    var phoneEl = form.querySelector("#bav-phone");
    var msgEl = form.querySelector("#bav-message");
    var dialEl = form.querySelector(".country-select");
    var agreeEl = form.querySelector(".checkbox-root");
    var submitBtn = form.querySelector("[data-enquiry-submit]");
    var okEl = form.querySelector(".success-msg");
    var errEl = form.querySelector(".error-msg");
    var busy = false;

    function setErr(msg) {
      if (errEl) {
        errEl.style.display = "";
        errEl.innerHTML = msg;
      }
      if (okEl) okEl.style.display = "none";
    }

    if ((msgEl.value || "").trim().length < 10) {
      setErr("Message must be at least 10 characters");
      return;
    }
    if (busy) return;
    busy = true;
    submitBtn.disabled = true;
    submitBtn.querySelector("span").textContent = "Sending…";

    fetch("/api/inquiries", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
      body: "json=" + encodeURIComponent(JSON.stringify({
        kind: "viewing",
        name: (nameEl.value || "").trim(),
        email: (emailEl.value || "").trim(),
        phone: ((dialEl ? dialEl.value : "+971") + " " + (phoneEl.value || "").trim()).trim(),
        message: (msgEl.value || "").trim(),
        property_ref: form.getAttribute("data-property-ref") || "",
        property_slug: form.getAttribute("data-property-slug") || "",
      })),
    })
      .then(function (res) {
        return res.json().catch(function () { return null; }).then(function (d) {
          if (!res.ok) {
            setErr((d && d.error) || "Something went wrong. Please try again.");
            return;
          }
          if (errEl) errEl.style.display = "none";
          if (okEl) okEl.style.display = "";
          nameEl.value = "";
          emailEl.value = "";
          phoneEl.value = "";
          msgEl.value = "";
          if (agreeEl) agreeEl.checked = false;
        });
      })
      .catch(function () {
        setErr("Something went wrong. Please try again.");
      })
      .finally(function () {
        busy = false;
        submitBtn.disabled = false;
        submitBtn.querySelector("span").textContent = "Request Information";
      });
  });

  /* ---------- BookViewingForm (book-a-viewing page) ---------- */

  document.addEventListener("submit", function (e) {
    var form = e.target;
    if (!form.matches || !form.matches("[data-bv-form]")) return;
    e.preventDefault();
    var nameEl = form.querySelector("#bv-name");
    var emailEl = form.querySelector("#bv-email");
    var phoneEl = form.querySelector("#bv-phone");
    var dialEl = form.querySelector(".bv-country");
    var dateEl = form.querySelector("#bv-date");
    var timeEl = form.querySelector("#bv-time");
    var langEl = form.querySelector("#bv-language");
    var msgEl = form.querySelector("#bv-message");
    var mortgageEl = form.querySelector("#bv-mortgage");
    var submitBtn = form.querySelector(".bv-submit");
    var failEl = form.querySelector(".bv-fail");
    var successEl = form.querySelector(".bv-success");
    var busy = false;

    function setErr(msg) {
      if (failEl) {
        failEl.style.display = "";
        failEl.textContent = msg;
      }
    }

    function fieldError(sel, msg) {
      var wrap = form.querySelector(sel + ", [data-err-for='" + sel + "']");
      if (!wrap) return;
      var err = wrap.parentNode.querySelector(".bv-error");
      if (err) err.remove();
      err = document.createElement("span");
      err.className = "bv-error";
      err.textContent = msg;
      wrap.parentNode.insertBefore(err, wrap.nextSibling);
    }

    var name = (nameEl ? nameEl.value : "").trim();
    var email = (emailEl ? emailEl.value : "").trim();
    var phone = (phoneEl ? phoneEl.value : "").trim();
    var date = dateEl ? dateEl.value : "";
    var time = timeEl ? timeEl.value : "";
    var ok = true;

    if (name.length < 2) { fieldError("#bv-name", "Please enter your full name"); ok = false; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { fieldError("#bv-email", "Please enter a valid email address"); ok = false; }
    if (phone.replace(/\D/g, "").length < 7) { fieldError("#bv-phone", "Please enter a valid phone number"); ok = false; }
    if (!date) { fieldError("#bv-date", "Please select a viewing date"); ok = false; }
    else {
      var today = new Date();
      today.setHours(0, 0, 0, 0);
      var pick = new Date(date + "T00:00:00");
      if (pick < today) { fieldError("#bv-date", "Please select today or a future date"); ok = false; }
    }
    if (!time) { fieldError("#bv-time", "Please select a viewing time"); ok = false; }
    if (!ok) return;
    if (busy) return;
    busy = true;
    if (submitBtn) submitBtn.disabled = true;
    if (failEl) failEl.style.display = "none";

    var title = form.getAttribute("data-property-title") || form.getAttribute("data-property-ref") || "";
    var fmtDate = date
      ? new Date(date + "T00:00:00").toLocaleDateString("en-GB", { weekday: "short", day: "numeric", month: "short", year: "numeric" })
      : "";
    var message = "";
    if (title) message += "Viewing request for \"" + title + "\"\n";
    message += "Preferred date: " + fmtDate + "\n"
      + "Preferred time: " + time + "\n"
      + "Preferred language: " + (langEl ? langEl.value : "") + "\n"
      + "Interested in mortgage advice: " + (mortgageEl && mortgageEl.checked ? "Yes" : "No");
    var notes = (msgEl ? msgEl.value : "").trim();
    if (notes) message += "\nMessage: " + notes;

    fetch("/api/inquiries", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
      body: "json=" + encodeURIComponent(JSON.stringify({
        kind: "viewing",
        name: name,
        email: email,
        phone: ((dialEl ? dialEl.value : "+971") + " " + phone).trim(),
        message: message,
        property_ref: form.getAttribute("data-property-ref") || "",
        property_slug: form.getAttribute("data-property-slug") || "",
      })),
    })
      .then(function (res) {
        return res.json().catch(function () { return null; }).then(function (d) {
          if (!res.ok) {
            setErr((d && d.error) || "Something went wrong. Please try again.");
            return;
          }
          if (successEl) successEl.style.display = "";
          if (form) form.style.display = "none";
        });
      })
      .catch(function () {
        setErr("Something went wrong. Please try again.");
      })
      .finally(function () {
        busy = false;
        if (submitBtn) {
          submitBtn.disabled = false;
          var s = submitBtn.querySelector("span");
          if (s) s.textContent = "Submit Details";
        }
      });
  });

  /* ---------- SaveButton (button + circle variants) ---------- */

  function readSavedState(wrap) {
    var ref = wrap.getAttribute("data-save-ref");
    fetch("/api/user/saved?ref=" + encodeURIComponent(ref || ""))
      .then(function (r) { return r.json().catch(function () { return null; }); })
      .then(function (d) { applySaved(wrap, !!d && !!d.saved); })
      .catch(function () { applySaved(wrap, false); });
  }

  function applySaved(wrap, saved) {
    if (wrap.classList.contains("detail-save-btn")) {
      wrap.classList.toggle("saved", saved);
      var label = wrap.childNodes[wrap.childNodes.length - 1];
      if (label && label.nodeType === 3) label.textContent = saved ? "Saved" : "Save property";
      var heart = wrap.querySelector("svg path");
      if (heart) {
        heart.setAttribute("fill", saved ? "#EE7133" : "none");
        heart.setAttribute("stroke", saved ? "#EE7133" : "currentColor");
      }
    } else {
      wrap.classList.toggle("saved", saved);
      var a = wrap.querySelector("a");
      if (a) {
        a.title = saved ? "Remove from saved" : "Save this property";
        a.setAttribute("aria-label", saved ? "Remove from saved" : "Save this property");
      }
    }
  }

  document.addEventListener("click", function (e) {
    var el = e.target;
    var wrap = el && el.closest ? el.closest("[data-save-wrap], .detail-save-btn") : null;
    if (!wrap) return;
    e.preventDefault();
    e.stopPropagation();
    if (wrap.dataset.saveBusy) return;
    wrap.dataset.saveBusy = "1";
    var saved = wrap.classList.contains("saved");
    fetch("/api/user/saved", {
      method: saved ? "DELETE" : "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
      body: "json=" + encodeURIComponent(JSON.stringify({
        property_ref: wrap.getAttribute("data-save-ref") || "",
        property_slug: wrap.getAttribute("data-save-slug") || "",
        title: wrap.getAttribute("data-save-title") || "",
        price: parseInt(wrap.getAttribute("data-save-price") || "0", 10) || 0,
        thumb: wrap.getAttribute("data-save-thumb") || "",
      })),
    })
      .then(function (res) {
        if (res.status === 401) {
          window.location.href = "/login/";
          return;
        }
        return res.json().catch(function () { return {}; }).then(function (d) {
          if (res.ok) applySaved(wrap, !!d.saved || !saved);
        });
      })
      .catch(function () {})
      .finally(function () { delete wrap.dataset.saveBusy; });
  });

  document.querySelectorAll("[data-save-wrap], .detail-save-btn").forEach(readSavedState);
})();