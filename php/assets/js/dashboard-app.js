// dashboard-app.js — portal dashboard wiring (port of portal-shell.tsx + dashboard-app.tsx client behavior)

(function () {
  "use strict";

  const $ = (sel, root) => (root || document).querySelector(sel);
  const $$ = (sel, root) => Array.prototype.slice.call((root || document).querySelectorAll(sel));

  const sidebar = $("[data-portal-sidebar]");
  const backdrop = $("[data-portal-backdrop]");
  const burger = $("[data-portal-burger]");
  const userMenu = $("[data-user-menu]");
  const userDropdown = $("[data-user-dropdown]");
  const toastEl = $("[data-toast]");
  const profileToast = $("[data-profile-toast]");

  function showToast(el, text) {
    if (!el) return;
    el.textContent = text;
    el.hidden = false;
    clearTimeout(el.__t);
    el.__t = setTimeout(function () { el.hidden = true; }, 2000);
  }

  function postLogout() {
    fetch("/api/auth/logout", { method: "POST", credentials: "same-origin" })
      .catch(function () {})
      .finally(function () { window.location.href = "/"; });
  }

  function setPanel(key) {
    $$("[data-portal-panel]").forEach(function (p) {
      p.hidden = p.getAttribute("data-portal-panel") !== key;
    });
    $$("[data-portal-tab]").forEach(function (b) {
      b.classList.toggle("active", b.getAttribute("data-portal-tab") === key);
    });
    if (userDropdown && !userDropdown.hidden) userDropdown.hidden = true;
    if (userMenu) userMenu.setAttribute("aria-expanded", "false");
    if (sidebar) sidebar.classList.remove("open");
    if (backdrop) backdrop.classList.remove("open");
    if (key === "settings") loadProfile();
    else if (key === "overview") loadSaved();
  }

  $$("[data-portal-tab]").forEach(function (b) {
    b.addEventListener("click", function () { setPanel(b.getAttribute("data-portal-tab")); });
  });

  $$("[data-logout]").forEach(function (b) { b.addEventListener("click", postLogout); });

  if (burger) burger.addEventListener("click", function () {
    if (sidebar) sidebar.classList.toggle("open");
    if (backdrop) backdrop.classList.toggle("open");
  });
  if (backdrop) backdrop.addEventListener("click", function () {
    if (sidebar) sidebar.classList.remove("open");
    backdrop.classList.remove("open");
  });
  if (userMenu) userMenu.addEventListener("click", function () {
    if (!userDropdown) return;
    userDropdown.hidden = !userDropdown.hidden;
    userMenu.setAttribute("aria-expanded", String(!userDropdown.hidden));
  });
  document.addEventListener("click", function (e) {
    if (userDropdown && !userDropdown.hidden && !e.target.closest(".shell-user-menu")) {
      userDropdown.hidden = true;
      if (userMenu) userMenu.setAttribute("aria-expanded", "false");
    }
  });

  /* ------------------------------ overview / wishlist ------------------------------ */

  const savedRender = $("[data-saved-render]");

  function fmtDate(s) {
    if (!s) return "";
    const d = new Date(s);
    if (isNaN(d.getTime())) return "";
    return d.toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
  }

  function fmtPrice(p) {
    if (p === null || p === undefined || p === "") return "";
    return "AED " + Number(p).toLocaleString();
  }

  function escHtml(s) {
    return String(s === null || s === undefined ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  function renderSaved(items) {
    if (!savedRender) return;
    if (!items || items.length === 0) {
      savedRender.innerHTML =
        '<div class="myprop-card">' +
        '<span class="myprop-card-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.47 6.47 0 0 0 1.52-4.23C15.95 6.57 13.38 4 10.24 4 7.11 4 4.55 6.57 4.55 9.5S7.11 15 10.24 15c1.61 0 3.1-.58 4.23-1.57l.27.28v.79l5 5L20.49 19l-4.99-5zm-5.26 0C8.07 14 6.11 12.03 6.11 9.5S8.07 5 10.24 5s4.13 1.97 4.13 4.5-1.96 4.5-4.13 4.5z"/></svg></span>' +
        '<div class="myprop-card-body">' +
        "<h3>You do not have any saved properties yet</h3>" +
        "<p>Tap the heart icon on any property to save it here, so you can compare options and shortlist your favourites.</p>" +
        '</div><a class="myprop-cta" href="/">Search &amp; Save</a></div>';
      return;
    }
    let html =
      '<p class="myprop-list-title">' + items.length + " saved propert" + (items.length === 1 ? "y" : "ies") + "</p>" +
      '<div class="myprop-list">';
    items.forEach(function (it) {
      const title = it.title || it.property_slug || it.property_ref || "";
      const price = fmtPrice(it.price);
      const date = fmtDate(it.created_at);
      const href = it.property_ref || "/";
      html +=
        '<div class="myprop-item" data-ref="' + escHtml(it.property_ref) + '">' +
        '<div class="myprop-item-main">' +
        '<div class="myprop-item-title">' + escHtml(title) + "</div>" +
        '<div class="myprop-item-sub">' + (price ? escHtml(price) + " &middot; " : "") + escHtml(date) + "</div>" +
        '</div><div class="myprop-item-actions">' +
        '<a class="myprop-ghost" href="' + escHtml(href) + '">View</a>' +
        '<button type="button" class="myprop-ghost danger" data-remove-ref="' + escHtml(it.property_ref) + '">Remove</button>' +
        "</div></div>";
    });
    html +=
      "</div>" +
      '<div class="myprop-card" style="margin-top:20px">' +
      '<span class="myprop-card-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.47 6.47 0 0 0 1.52-4.23C15.95 6.57 13.38 4 10.24 4 7.11 4 4.55 6.57 4.55 9.5S7.11 15 10.24 15c1.61 0 3.1-.58 4.23-1.57l.27.28v.79l5 5L20.49 19l-4.99-5zm-5.26 0C8.07 14 6.11 12.03 6.11 9.5S8.07 5 10.24 5s4.13 1.97 4.13 4.5-1.96 4.5-4.13 4.5z"/></svg></span>' +
      '<div class="myprop-card-body">' +
      "<h3>Looking for something new?</h3>" +
      "<p>Head back to the property search to shortlist more homes.</p>" +
      '</div><a class="myprop-cta" href="/">Search &amp; Save</a></div>';
    savedRender.innerHTML = html;
    $$("[data-remove-ref]", savedRender).forEach(function (b) {
      b.addEventListener("click", function () { removeSaved(b.getAttribute("data-remove-ref")); });
    });
  }

  function loadSaved() {
    fetch("/api/user/saved", { credentials: "same-origin" })
      .then(function (r) { return r.json(); })
      .then(function (d) { renderSaved(d.items || []); })
      .catch(function () { renderSaved([]); });
  }

  function removeSaved(ref) {
    fetch("/api/user/saved", {
      method: "DELETE",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ property_ref: ref }),
    })
      .then(function () {
        showToast(toastEl, "Removed from saved");
        loadSaved();
      })
      .catch(function () {});
  }

  $$("[data-myprop-view]").forEach(function (b) {
    b.addEventListener("click", function () {
      const v = b.getAttribute("data-myprop-view");
      $$("[data-myprop-view]").forEach(function (x) { x.classList.toggle("active", x === b); });
      $$("[data-myprop-panel]").forEach(function (p) {
        p.hidden = p.getAttribute("data-myprop-panel") !== v;
      });
    });
  });

  loadSaved();

  /* ------------------------------ profile ------------------------------ */

  const COUNTRIES = Array.prototype.slice.call($$("option", $("[data-country-select]"))).map(function (o) {
    return { code: o.value, dial: o.textContent.replace(/^\S+\s/, "") };
  });

  const profileForm = $("[data-profile-form]");
  const prefsForm = $("[data-prefs-form]");
  const passwordForm = $("[data-password-form]");
  const deleteForm = $("[data-delete-form]");
  const countrySelect = $("[data-country-select]");
  const phoneInput = profileForm ? $('input[name="phone"]', profileForm) : null;
  let selectedDial = "+971";
  let prefs = { subscribe_news: true, email_notifications: true, property_alerts: true };

  function showProfileToast(ok, text) {
    if (!profileToast) return;
    profileToast.textContent = text;
    profileToast.hidden = false;
    profileToast.style.background = ok ? "#1e6f2e" : "#b3261e";
    clearTimeout(profileToast.__t);
    profileToast.__t = setTimeout(function () { profileToast.hidden = true; }, 3000);
  }

  function loadProfile() {
    fetch("/api/user/profile", { credentials: "same-origin" })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        const u = d.user || {};
        if (profileForm) {
          $('input[name="first_name"]', profileForm).value = u.first_name || "";
          $('input[name="surname"]', profileForm).value = u.surname || "";
          $('input[name="email"]', profileForm).value = u.email || "";
          const a = d.address || {};
          $('input[name="address_line1"]', profileForm).value = a.address_line1 || "";
          $('input[name="address_line2"]', profileForm).value = a.address_line2 || "";
          $('input[name="town_city"]', profileForm).value = a.town_city || "";
          $('input[name="postcode"]', profileForm).value = a.postcode || "";
          $('input[name="address_country"]', profileForm).value = a.country || "";
          const phone = u.phone || "";
          let local = phone;
          if (countrySelect) {
            for (let i = 0; i < countrySelect.options.length; i++) {
              const dial = countrySelect.options[i].textContent.replace(/^\S+\s/, "");
              if (phone.indexOf(dial) === 0) {
                countrySelect.selectedIndex = i;
                selectedDial = dial;
                local = phone.substring(dial.length).trim();
                break;
              }
            }
          }
          if (phoneInput) phoneInput.value = local;
        }
        if (d.preferences) {
          prefs = d.preferences;
          $$("[data-toggle]").forEach(function (t) {
            const key = t.getAttribute("data-toggle");
            const on = !!prefs[key];
            t.classList.toggle("active", on);
            t.setAttribute("aria-checked", String(on));
          });
        }
      })
      .catch(function () {});
  }

  if (countrySelect) {
    countrySelect.addEventListener("change", function () {
      selectedDial = countrySelect.options[countrySelect.selectedIndex].textContent.replace(/^\S+\s/, "");
    });
  }

  if (profileForm) {
    profileForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const data = {
        first_name: $('input[name="first_name"]', profileForm).value.trim(),
        surname: $('input[name="surname"]', profileForm).value.trim(),
        email: $('input[name="email"]', profileForm).value.trim(),
        phone: selectedDial + " " + (phoneInput ? phoneInput.value.trim() : ""),
        address: {
          address_line1: $('input[name="address_line1"]', profileForm).value,
          address_line2: $('input[name="address_line2"]', profileForm).value,
          town_city: $('input[name="town_city"]', profileForm).value,
          postcode: $('input[name="postcode"]', profileForm).value,
          country: $('input[name="address_country"]', profileForm).value,
        },
        preferences: prefs,
      };
      const btn = $('button[type="submit"]', profileForm);
      btn.disabled = true;
      fetch("/api/user/profile", {
        method: "PUT",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      })
        .then(function (r) {
          return r.json().catch(function () { return {}; }).then(function (d) {
            return { ok: r.ok, d: d };
          });
        })
        .then(function (res) {
          showProfileToast(res.ok, res.ok ? "Profile updated" : (res.d.error || "Update failed"));
        })
        .catch(function () { showProfileToast(false, "Network error"); })
        .finally(function () { btn.disabled = false; });
    });
  }

  $$("[data-toggle]").forEach(function (t) {
    t.addEventListener("click", function () {
      const key = t.getAttribute("data-toggle");
      prefs[key] = !prefs[key];
      t.classList.toggle("active", prefs[key]);
      t.setAttribute("aria-checked", String(prefs[key]));
    });
  });

  if (prefsForm) {
    prefsForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const btn = $('button[type="submit"]', prefsForm);
      btn.disabled = true;
      fetch("/api/user/notifications", {
        method: "PUT",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ preferences: prefs }),
      })
        .then(function (r) {
          return r.json().catch(function () { return {}; }).then(function (d) {
            return { ok: r.ok, d: d };
          });
        })
        .then(function (res) {
          showProfileToast(res.ok, res.ok ? "Preferences updated" : (res.d.error || "Update failed"));
        })
        .catch(function () { showProfileToast(false, "Network error"); })
        .finally(function () { btn.disabled = false; });
    });
  }

  /* password strength + show/hide + submit */
  function strengthOf(pw) {
    let s = 0;
    if (pw.length >= 8) s += 1;
    if (/[A-Z]/.test(pw)) s += 1;
    if (/[a-z]/.test(pw)) s += 1;
    if (/\d/.test(pw)) s += 1;
    if (/[^A-Za-z0-9]/.test(pw)) s += 1;
    return Math.min(5, s);
  }

  const pwNew = $("[data-pw-new]");
  const pwConfirm = $("[data-pw-confirm]");
  const strengthText = $("[data-strength-text]");

  if (pwNew) {
    pwNew.addEventListener("input", function () {
      const s = strengthOf(pwNew.value);
      $$("[data-bar]").forEach(function (b) {
        const i = Number(b.getAttribute("data-bar"));
        b.classList.toggle("active", i <= s);
        b.style.backgroundColor = i <= s ? (i <= 2 ? "#b3261e" : i <= 4 ? "#ff9800" : "#1e6f2e") : "#e0e0e0";
      });
      if (strengthText) strengthText.textContent = s <= 2 ? "Weak" : s <= 4 ? "Medium" : "Strong";
    });
  }

  $$("[data-pw-toggle]").forEach(function (b) {
    b.addEventListener("click", function () {
      const target = b.closest(".password-input") ? $('input', b.closest(".password-input")) : null;
      if (!target) return;
      const show = target.type === "password";
      target.type = show ? "text" : "password";
      b.innerHTML = show
        ? '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.94 17.94A9.99 9.99 0 0 1 12 20c-5.52 0-10-4.48-10-10 0-1.1.22-2.15.6-3.12l1.46 1.46C2.67 10.25 2 11.15 2 12c0 5.52 4.48 10 10 10 1.1 0 2.15-.22 3.12-.6l1.46 1.46c-.97.53-2.03.9-3.12.9zM13.06 8.06L15.12 10.12C15.32 10.32 15.5 10.56 15.5 10.83c0 1.66-1.34 3-3 3-.27 0-.53-.04-.78-.12L10.94 12.94C11.14 12.74 11.32 12.5 11.32 12.17c0-1.66 1.34-3 3-3 .27 0 .53.04.78.12zM1.1 10.06l1.46-1.46C2.67 7.75 2 8.65 2 9.5c0 1.1.22 2.15.6 3.12L1.1 10.06zM12 7c-2.76 0-5 2.24-5 5 0 1.1.22 2.15.6 3.12l1.46-1.46C8.67 12.25 8 11.35 8 10.5c0-1.1-.22-2.15-.6-3.12L7.06 7.06C7.4 6.7 7.73 6.5 8 6.5c1.66 0 3 1.34 3 3 0 .27.04.53.12.78l1.46-1.46C12.33 8.75 12 8.15 12 7.5c0-.27-.04-.53-.12-.78L13.06 8.06z"/></svg>';
    });
  });

  if (passwordForm) {
    passwordForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const np = pwNew ? pwNew.value : "";
      const cp = pwConfirm ? pwConfirm.value : "";
      if (np !== cp) { showProfileToast(false, "New passwords do not match"); return; }
      if (np.length < 8) { showProfileToast(false, "Password must be at least 8 characters"); return; }
      if (!/[A-Za-z]/.test(np) || !/\d/.test(np)) {
        showProfileToast(false, "Password must contain letters and numbers");
        return;
      }
      const btn = $('button[type="submit"]', passwordForm);
      btn.disabled = true;
      fetch("/api/user/password", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ new_password: np, confirm_password: cp }),
      })
        .then(function (r) {
          return r.json().catch(function () { return {}; }).then(function (d) {
            return { ok: r.ok, d: d };
          });
        })
        .then(function (res) {
          if (res.ok) {
            if (pwNew) pwNew.value = "";
            if (pwConfirm) pwConfirm.value = "";
            showProfileToast(true, "Password changed. You have been signed out of other sessions.");
          } else {
            showProfileToast(false, res.d.error || "Change failed");
          }
        })
        .catch(function () { showProfileToast(false, "Network error"); })
        .finally(function () { btn.disabled = false; });
    });
  }

  if (deleteForm) {
    deleteForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!window.confirm("Are you sure you want to delete your account? This cannot be undone.")) return;
      const reason = $('textarea[name="reason"]', deleteForm).value;
      fetch("/api/user/account", {
        method: "DELETE",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ reason: reason }),
      })
        .then(function (r) {
          if (r.ok) { window.location.href = "/"; }
          else {
            return r.json().catch(function () { return {}; }).then(function (d) {
              showProfileToast(false, d.error || "Deletion failed");
            });
          }
        })
        .catch(function () { showProfileToast(false, "Network error"); });
    });
  }

  $$("[data-profile-tab]").forEach(function (b) {
    b.addEventListener("click", function () {
      const key = b.getAttribute("data-profile-tab");
      $$("[data-profile-tab]").forEach(function (x) { x.classList.toggle("active", x === b); });
      $$("[data-profile-panel]").forEach(function (p) {
        p.hidden = p.getAttribute("data-profile-panel") !== key;
      });
    });
  });
})();