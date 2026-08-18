/* admin-app.js — admin panel behavior (port of src/components/admin/admin-app.tsx)
 * Static shell + schemas are server-rendered into window.ADMIN_BOOT by admin/index.php. */

(function () {
  "use strict";

  var BOOT = window.ADMIN_BOOT || { schemas: { fields: {}, columns: {} }, user: {} };
  var FIELDS = BOOT.schemas.fields || {};
  var COLUMNS = BOOT.schemas.columns || {};

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  var toastEl = $("[data-toast]");
  var modalRoot = $("[data-modal-root]");

  function esc(s) {
    return String(s === null || s === undefined ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  function showToast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.hidden = false;
    clearTimeout(toastEl.__t);
    toastEl.__t = setTimeout(function () { toastEl.hidden = true; }, 2200);
  }

  function fmtDate(s) {
    if (!s) return "";
    var d = new Date(s);
    if (isNaN(d.getTime())) return "";
    return d.toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
  }

  function fmtPrice(p) {
    var n = Number(p);
    return n ? "AED " + n.toLocaleString("en-US") : "—";
  }

  function api(url, opts) {
    var o = opts || {};
    o.credentials = "same-origin";
    return fetch(url, o).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (d) {
        return { ok: r.ok, status: r.status, data: d };
      });
    });
  }

  function singular(title) {
    if (/ies$/.test(title)) return title.slice(0, -3) + "y";
    if (/s$/.test(title)) return title.slice(0, -1);
    return title;
  }

  function parseListingPayload(raw) {
    try {
      var d = JSON.parse(raw);
      if (d && typeof d === "object") {
        var out = {};
        Object.keys(d).forEach(function (k) {
          var v = d[k];
          if (typeof v === "string" && v.trim() !== "") out[k] = v;
        });
        return Object.keys(out).length ? out : null;
      }
    } catch (e) { /* fall through */ }
    return null;
  }

  /* ------------------------------ shell ------------------------------ */

  var sidebar = $("[data-portal-sidebar]");
  var backdrop = $("[data-portal-backdrop]");
  var burger = $("[data-portal-burger]");
  var userMenu = $("[data-user-menu]");
  var userDropdown = $("[data-user-dropdown]");

  function logout() {
    api("/api/auth/logout", { method: "POST" })
      .catch(function () {})
      .then(function () { window.location.href = "/"; });
  }

  $$("[data-logout]").forEach(function (b) { b.addEventListener("click", logout); });
  var pl = $("[data-profile-link]");
  if (pl) pl.addEventListener("click", function () { window.location.href = "/dashboard"; });

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

  /* ------------------------------ modal ------------------------------ */

  function closeModal() {
    if (modalRoot) modalRoot.innerHTML = "";
    document.body.classList.remove("modal-open");
  }

  function openModal(title, bodyBuilder) {
    if (!modalRoot) return;
    var backdropEl = document.createElement("div");
    backdropEl.className = "app-modal-backdrop";
    backdropEl.addEventListener("click", closeModal);
    var modal = document.createElement("div");
    modal.className = "app-modal";
    modal.addEventListener("click", function (e) { e.stopPropagation(); });
    var head = document.createElement("div");
    head.className = "app-modal-head";
    var h = document.createElement("h3");
    h.textContent = title;
    var closeBtn = document.createElement("button");
    closeBtn.className = "app-modal-close";
    closeBtn.setAttribute("aria-label", "Close");
    closeBtn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>';
    closeBtn.addEventListener("click", closeModal);
    head.appendChild(h);
    head.appendChild(closeBtn);
    var body = document.createElement("div");
    body.className = "app-modal-body";
    bodyBuilder(body);
    modal.appendChild(head);
    modal.appendChild(body);
    backdropEl.appendChild(modal);
    modalRoot.appendChild(backdropEl);
    document.body.classList.add("modal-open");
  }

  /* ------------------------------ column renderers ------------------------------ */

  function renderCell(row, col) {
    var v = row[col.key];
    switch (col.kind) {
      case "strong": return "<strong>" + esc(v) + "</strong>";
      case "muted": return '<span style="color:#9399a4">' + esc(v) + "</span>";
      case "small": return '<span style="font-size:12px">' + esc(v) + "</span>";
      case "dash": return v === null || v === undefined || v === "" ? "—" : esc(v);
      case "breakall": return '<span style="word-break:break-all">' + esc(v) + "</span>";
      case "price": return fmtPrice(v);
      case "badge": return '<span class="app-badge">' + esc(v || "—") + "</span>";
      case "stars": {
        var n = Math.min(5, Number(v) || 0), s = "";
        for (var i = 0; i < n; i++) s += "★";
        return s || "—";
      }
      case "bool": {
        var on = Number(v) === 1 || v === true;
        return '<span class="app-badge ' + (on ? "active" : "inactive") + '">' + (on ? "yes" : "no") + "</span>";
      }
      default: return esc(v);
    }
  }

  function resourceTable(items, cols) {
    var h = '<div style="overflow-x:auto"><table class="app-table"><thead><tr>';
    cols.forEach(function (c) { h += "<th>" + esc(c.label) + "</th>"; });
    h += "<th></th></tr></thead><tbody>";
    items.forEach(function (row) {
      h += "<tr>";
      cols.forEach(function (c) { h += "<td>" + renderCell(row, c) + "</td>"; });
      h += '<td><div class="row-actions">' +
        '<button type="button" class="app-btn ghost sm" data-edit="' + row.id + '">Edit</button>' +
        '<button type="button" class="app-btn danger sm" data-del="' + row.id + '">Delete</button>' +
        "</div></td></tr>";
    });
    h += "</tbody></table></div>";
    return h;
  }

  /* ------------------------------ FormPage ------------------------------ */

  function buildForm(container, fields, initial, opts) {
    opts = opts || {};
    var form = document.createElement("form");
    form.className = "app-form-grid";
    form.setAttribute("novalidate", "");
    var state = {};
    fields.forEach(function (fd) {
      var val = initial[fd.key];
      state[fd.key] = fd.type === "checkbox" ? Boolean(Number(val)) : (val === undefined || val === null ? "" : val);
    });
    fields.forEach(function (fd) {
      var wrap = document.createElement("div");
      wrap.className = "app-field" + (fd.full ? " full" : "");
      var label = document.createElement("label");
      label.textContent = fd.label;
      wrap.appendChild(label);
      var control;
      if (fd.type === "textarea") {
        control = document.createElement("textarea");
        control.value = state[fd.key];
        control.addEventListener("input", function () { state[fd.key] = control.value; });
      } else if (fd.type === "number") {
        control = document.createElement("input");
        control.type = "number";
        control.value = state[fd.key];
        control.addEventListener("input", function () { state[fd.key] = control.value; });
      } else if (fd.type === "select") {
        control = document.createElement("select");
        var empty = document.createElement("option");
        empty.value = "";
        empty.textContent = "—";
        control.appendChild(empty);
        (fd.options || []).forEach(function (o) {
          var opt = document.createElement("option");
          opt.value = o; opt.textContent = o;
          control.appendChild(opt);
        });
        control.value = state[fd.key];
        control.addEventListener("change", function () { state[fd.key] = control.value; });
      } else if (fd.type === "checkbox") {
        var row = document.createElement("div");
        row.className = "app-check-row";
        control = document.createElement("input");
        control.type = "checkbox";
        control.checked = state[fd.key];
        control.addEventListener("change", function () { state[fd.key] = control.checked ? 1 : 0; });
        var span = document.createElement("span");
        span.style.fontSize = "13px";
        span.textContent = fd.hint || "Enabled";
        row.appendChild(control);
        row.appendChild(span);
        wrap.appendChild(row);
        if (opts.onFieldState) opts.onFieldState(fd.key, function () { return state[fd.key]; });
        form.appendChild(wrap);
        return;
      } else if (fd.type === "json") {
        control = document.createElement("input");
        control.type = "text";
        control.placeholder = "Comma separated values";
        control.value = Array.isArray(state[fd.key]) ? state[fd.key].join(", ") : (state[fd.key] || "");
        control.addEventListener("input", function () { state[fd.key] = control.value; });
      } else {
        control = document.createElement("input");
        control.type = "text";
        control.value = state[fd.key];
        control.addEventListener("input", function () { state[fd.key] = control.value; });
      }
      wrap.appendChild(control);
      if (fd.hint && fd.type !== "checkbox") {
        var hint = document.createElement("div");
        hint.className = "hint";
        hint.textContent = fd.hint;
        wrap.appendChild(hint);
      }
      form.appendChild(wrap);
    });
    var actions = document.createElement("div");
    actions.className = "form-actions full";
    var cancel = document.createElement("button");
    cancel.type = "button";
    cancel.className = "app-btn ghost";
    cancel.textContent = "Cancel";
    cancel.addEventListener("click", opts.onCancel);
    var save = document.createElement("button");
    save.type = "submit";
    save.className = "app-btn";
    save.textContent = opts.saveLabel || "Save";
    actions.appendChild(cancel);
    actions.appendChild(save);
    form.appendChild(actions);
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (opts.onSave) opts.onSave(state, save);
    });
    container.appendChild(form);
    return { state: state, form: form, saveBtn: save };
  }

  function formPageCard(container, title, backLabel, fields, initial, onSave, onCancel) {
    var card = document.createElement("div");
    card.className = "app-card";
    var head = document.createElement("div");
    head.className = "app-card-head";
    var h2 = document.createElement("h2");
    h2.textContent = title;
    var back = document.createElement("button");
    back.type = "button";
    back.className = "app-btn ghost sm form-page-back";
    back.textContent = "← Back to " + backLabel;
    back.addEventListener("click", onCancel);
    head.appendChild(h2);
    head.appendChild(back);
    card.appendChild(head);
    buildForm(card, fields, initial, {
      onSave: function (state, saveBtn) { onSave(state, saveBtn); },
      onCancel: onCancel,
    });
    container.appendChild(card);
  }

  /* ------------------------------ generic ResourceManager ------------------------------ */

  function initResource(panel, endpoint, title) {
    var fields = (BOOT.schemas.fields || {})[endpoint] || [];
    var cols = (BOOT.schemas.columns || {})[endpoint] || [];
    var items = null, q = "", editing = null, creating = false;
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);

    function load(query) {
      var url = "/api/admin/" + endpoint + (query ? "?q=" + encodeURIComponent(query) : "");
      api(url).then(function (res) {
        items = res.data.items || [];
        q = query;
        render();
      }).catch(function () { items = []; q = query; render(); });
    }

    function remove(row) {
      var label = row.title || row.name || row.author || row.question || row.url || row.id;
      if (!window.confirm('Delete "' + label + '"?')) return;
      api("/api/admin/" + endpoint + "/" + row.id, { method: "DELETE" }).then(function () {
        showToast("Deleted");
        load(q);
      });
    }

    function save(state, saveBtn) {
      saveBtn.disabled = true;
      var body = {};
      fields.forEach(function (fd) { body[fd.key] = state[fd.key]; });
      var url = "/api/admin/" + endpoint + (editing ? "/" + editing.id : "");
      var method = editing ? "PUT" : "POST";
      api(url, {
        method: method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      }).then(function (res) {
        saveBtn.disabled = false;
        if (!res.ok) { showToast(res.data.error || "Save failed"); return; }
        showToast(editing ? "Saved" : "Created");
        editing = null; creating = false;
        load(q);
      });
    }

    function render() {
      if (creating || editing) {
        card.innerHTML = "";
        formPageCard(card, editing ? "Edit " + singular(title) : "New " + singular(title), title, fields, editing || {}, save, function () {
          editing = null; creating = false; render();
        });
        return;
      }
      var head = '<div class="app-card-head"><div><h2>' + esc(title) + '</h2><p class="app-card-sub">' + (items ? items.length : 0) + " records</p></div>" +
        '<div style="display:flex;gap:10px">' +
        '<input class="app-search" placeholder="Search…" value="' + esc(q) + '" data-search>' +
        '<button type="button" class="app-btn" data-add>+ Add</button></div></div>';
      var body = items === null ? '<p class="app-empty">Loading…</p>' :
        items.length === 0 ? '<p class="app-empty">No records found.</p>' : resourceTable(items, cols);
      card.innerHTML = head + body;
      var inp = $("[data-search]", card);
      if (inp) inp.addEventListener("keydown", function (e) { if (e.key === "Enter") load(inp.value); });
      var add = $("[data-add]", card);
      if (add) add.addEventListener("click", function () { creating = true; render(); });
      $$("[data-edit]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-edit"));
          items.forEach(function (r) { if (Number(r.id) === id) editing = r; });
          render();
        });
      });
      $$("[data-del]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-del"));
          items.forEach(function (r) { if (Number(r.id) === id) remove(r); });
        });
      });
    }

    load(q);
    return { reload: function () { load(q); } };
  }

  /* ------------------------------ Stats overview ------------------------------ */

  function initStats(panel) {
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);
    card.innerHTML = '<p class="app-empty">Loading…</p>';
    api("/api/admin/stats").then(function (res) {
      var s = res.data.stats || {};
      var recent = res.data.recentInquiries || [];
      var tiles = [
        ["Properties", s.properties, (s.publishedProperties || 0) + " published"],
        ["Users", s.users, ""],
        ["Inquiries", s.inquiries, (s.newInquiries || 0) + " new"],
        ["Viewings", s.viewings, (s.pendingViewings || 0) + " pending"],
      ];
      var h = '<div class="app-stats">';
      tiles.forEach(function (t) {
        h += '<div class="app-stat"><div class="label">' + esc(t[0]) + '</div><div class="value">' + esc(t[1]) + "</div>" +
          (t[2] ? '<div class="sub">' + esc(t[2]) + "</div>" : "") + "</div>";
      });
      h += "</div>";
      var dir = [
        ["Services", s.services], ["Agents", s.agents], ["Developers", s.developers],
        ["Communities", s.communities], ["Testimonials", s.testimonials], ["FAQs", s.faqs],
        ["Media items", s.media], ["Saved properties", s.savedProperties],
      ];
      h += '<div class="app-card" style="margin-top:16px"><div class="app-card-head"><div><h2>Directory</h2></div></div><div class="app-stats">';
      dir.forEach(function (d) {
        h += '<div class="app-stat"><div class="label">' + esc(d[0]) + '</div><div class="value">' + esc(d[1]) + "</div></div>";
      });
      h += "</div></div>";
      if (recent.length) {
        h += '<div class="app-card" style="margin-top:16px"><div class="app-card-head"><div><h2>Recent inquiries</h2></div></div>' +
          '<div style="overflow-x:auto"><table class="app-table"><thead><tr><th>Name</th><th>Kind</th><th>Message</th><th>Status</th><th>Date</th></tr></thead><tbody>';
        recent.forEach(function (i) {
          var msg = String(i.message || "").slice(0, 60);
          h += "<tr><td><strong>" + esc(i.name) + "</strong><div style=\"font-size:12px;color:#9399a4\">" + esc(i.email) + "</div></td>" +
            "<td>" + esc(i.kind) + "</td><td>" + esc(msg) + "</td>" +
            '<td><span class="app-badge ' + esc(i.status) + '">' + esc(i.status) + "</span></td>" +
            "<td>" + esc(fmtDate(i.created_at)) + "</td></tr>";
        });
        h += "</tbody></table></div></div>";
      }
      card.innerHTML = h;
    }).catch(function () {
      card.innerHTML = '<p class="app-empty">Could not load stats.</p>';
    });
  }

  /* ------------------------------ Properties ------------------------------ */

  function initProperties(panel) {
    var items = null, q = "";
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);

    function load(query) {
      var url = "/api/admin/properties" + (query ? "?q=" + encodeURIComponent(query) : "");
      api(url).then(function (res) {
        items = res.data.items || [];
        q = query;
        render();
      }).catch(function () { items = []; q = query; render(); });
    }

    function remove(row) {
      if (!window.confirm('Delete "' + (row.title || row.id) + '"?')) return;
      api("/api/admin/properties?id=" + row.id, { method: "DELETE" }).then(function () {
        showToast("Deleted");
        load(q);
      });
    }

    function render() {
      var head = '<div class="app-card-head"><div><h2>Properties</h2><p class="app-card-sub">' + (items ? items.length : 0) + ' records — created properties appear on the public site</p></div>' +
        '<div style="display:flex;gap:10px">' +
        '<input class="app-search" placeholder="Search title, slug, developer…" value="' + esc(q) + '" data-search>' +
        '<button type="button" class="app-btn" data-add>+ New property</button></div></div>';
      if (items === null) { card.innerHTML = head + '<p class="app-empty">Loading…</p>'; }
      else if (items.length === 0) { card.innerHTML = head + '<p class="app-empty">No properties yet. Create your first one.</p>'; }
      else {
        var h = head + '<div style="overflow-x:auto"><table class="app-table"><thead><tr>' +
          "<th>Title</th><th>Type</th><th>Price</th><th>Beds</th><th>Media</th><th>Status</th><th></th></tr></thead><tbody>";
        items.forEach(function (p) {
          var path = "/" + esc(p.transaction_type || "buy") + "/" + esc(p.slug) + esc(p.id) + "/";
          var statusBadge = '<span class="app-badge ' + (Number(p.published) === 1 ? "active" : "inactive") + '">' + (Number(p.published) === 1 ? "published" : "draft") + "</span>";
          if (Number(p.featured) === 1) statusBadge += ' <span class="app-badge" style="background:#fff3e0;color:#b26a00">featured</span>';
          h += "<tr><td><strong>" + esc(p.title) + "</strong><div style=\"font-size:12px;color:#9399a4\">" + path + "</div></td>" +
            "<td>" + esc(p.property_type) + "</td><td>" + fmtPrice(p.price) + "</td>" +
            "<td>" + esc(p.bedroom) + " bd / " + esc(p.bathroom) + " ba</td>" +
            "<td>" + esc(p.image_count || 0) + " img &middot; " + esc(p.amenity_count || 0) + " am.</td>" +
            "<td>" + statusBadge + "</td>" +
            '<td><div class="row-actions">' +
            '<a class="app-btn ghost sm" href="' + path + '" target="_blank">View</a>' +
            '<button type="button" class="app-btn ghost sm" data-edit="' + p.id + '">Edit</button>' +
            '<button type="button" class="app-btn danger sm" data-del="' + p.id + '">Delete</button>' +
            "</div></td></tr>";
        });
        h += "</tbody></table></div>";
        card.innerHTML = h;
        $$("[data-del]", card).forEach(function (b) {
          b.addEventListener("click", function () {
            var id = Number(b.getAttribute("data-del"));
            items.forEach(function (r) { if (Number(r.id) === id) remove(r); });
          });
        });
      }
      var inp = $("[data-search]", card);
      if (inp) inp.addEventListener("keydown", function (e) { if (e.key === "Enter") load(inp.value); });
      var add = $("[data-add]", card);
      if (add) add.addEventListener("click", function () { openPropertyForm(null); });
      $$("[data-edit]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-edit"));
          api("/api/admin/properties/" + id).then(function (res) {
            if (!res.ok) { showToast("Could not load property"); return; }
            openPropertyForm(res.data.item);
          });
        });
      });
    }

    function openPropertyForm(initial) {
      card.innerHTML = "";
      var back = document.createElement("div");
      back.className = "app-card-head";
      var h2 = document.createElement("h2");
      h2.textContent = initial ? "Edit property" : "New property";
      var bb = document.createElement("button");
      bb.type = "button";
      bb.className = "app-btn ghost sm form-page-back";
      bb.textContent = "← Back to Properties";
      bb.addEventListener("click", function () { load(q); });
      back.appendChild(h2);
      back.appendChild(bb);
      card.appendChild(back);
      buildPropertyForm(card, initial, function () { load(q); });
    }

    load(q);
  }

  function buildPropertyForm(container, initial, onDone) {
    var isEdit = !!initial;
    var form = {};
    var agents = [], agentQuery = "", agentOpen = false, selectedAgent = null;
    var media = (initial && initial.media) ? initial.media.slice() : [];
    var amenityList = [], selectedAmenities = (initial && initial.amenities) ? initial.amenities.slice() : [];
    var uploading = { done: 0, total: 0 };

    var FIELDS = [
      { key: "title", label: "Title", required: true, full: true, hint: "e.g. 2 Bedroom Apartment in Dubai Marina" },
      { key: "slug", label: "Slug (optional — auto-generated from title)", full: true },
      { key: "transaction_type", label: "Transaction", type: "select", options: ["buy", "rent"] },
      { key: "property_type", label: "Property type", type: "select", options: ["apartment", "villa", "townhouse", "penthouse", "studio", "duplex", "mansion", "commercial-property", "plot"] },
      { key: "category", label: "Category", type: "select", options: ["apartments", "villas", "townhouses", "penthouses", "studios", "duplexes", "mansions", "commercial-properties", "plots"] },
      { key: "status", label: "Status", type: "select", options: ["ready", "off-plan", "under-construction"] },
      { key: "price", label: "Price (AED)", type: "number" },
      { key: "price_qualifier", label: "Price qualifier", type: "select", options: ["AED", "AED / yearly"] },
      { key: "community", label: "Community", full: true },
      { key: "developer", label: "Developer", full: true },
      { key: "location", label: "Location", full: true },
      { key: "display_address", label: "Display address", full: true },
      { key: "latitude", label: "Latitude", type: "number" },
      { key: "longitude", label: "Longitude", type: "number" },
      { key: "bedroom", label: "Bedrooms", type: "number" },
      { key: "bathroom", label: "Bathrooms", type: "number" },
      { key: "area_sqft", label: "Area (sq ft)", type: "number" },
      { key: "plot_size", label: "Plot size", type: "number" },
      { key: "parking", label: "Parking spots", type: "number" },
      { key: "furnished", label: "Furnishing", type: "select", options: ["Furnished", "Unfurnished", "Partially Furnished"] },
      { key: "completion_status", label: "Completion", type: "select", options: ["Ready", "Off-Plan", "Under Construction"] },
      { key: "year_built", label: "Year built", type: "number" },
      { key: "featured", label: "Featured", type: "checkbox", hint: "Show in featured sliders" },
      { key: "published", label: "Published", type: "checkbox", hint: "Visible on the public site" },
      { key: "introtext", label: "Short intro", type: "textarea", full: true },
      { key: "long_description", label: "Full description (HTML allowed)", type: "textarea", full: true },
    ];

    var root = document.createElement("div");
    root.className = "app-card";
    var wrap = document.createElement("div");
    wrap.className = "app-form-grid";

    FIELDS.forEach(function (fd) {
      var val = initial ? initial[fd.key] : "";
      form[fd.key] = fd.type === "checkbox" ? Boolean(Number(val)) : (val === undefined || val === null ? "" : val);
      var f = document.createElement("div");
      f.className = "app-field" + (fd.full ? " full" : "");
      var label = document.createElement("label");
      label.textContent = fd.label;
      f.appendChild(label);
      var control;
      if (fd.key === "agent_id") { /* handled after amenities below */ }
      else if (fd.type === "textarea") {
        control = document.createElement("textarea");
        control.value = form[fd.key];
        control.addEventListener("input", function () { form[fd.key] = control.value; });
      } else if (fd.type === "number") {
        control = document.createElement("input");
        control.type = "number";
        control.value = form[fd.key];
        control.addEventListener("input", function () { form[fd.key] = control.value; });
      } else if (fd.type === "select") {
        control = document.createElement("select");
        var empty = document.createElement("option");
        empty.value = ""; empty.textContent = "—";
        control.appendChild(empty);
        fd.options.forEach(function (o) {
          var opt = document.createElement("option");
          opt.value = o; opt.textContent = o;
          control.appendChild(opt);
        });
        control.value = form[fd.key];
        control.addEventListener("change", function () { form[fd.key] = control.value; });
      } else if (fd.type === "checkbox") {
        var row = document.createElement("div");
        row.className = "app-check-row";
        control = document.createElement("input");
        control.type = "checkbox";
        control.checked = form[fd.key];
        control.addEventListener("change", function () { form[fd.key] = control.checked ? 1 : 0; });
        var sp = document.createElement("span");
        sp.style.fontSize = "13px";
        sp.textContent = fd.hint || "Enabled";
        row.appendChild(control);
        row.appendChild(sp);
        f.appendChild(row);
        if (fd.hint) { var h2x = document.createElement("div"); h2x.className = "hint"; h2x.textContent = fd.hint; f.appendChild(h2x); }
        wrap.appendChild(f);
        return;
      } else {
        control = document.createElement("input");
        control.type = "text";
        control.value = form[fd.key];
        control.addEventListener("input", function () { form[fd.key] = control.value; });
      }
      f.appendChild(control);
      if (fd.hint) { var hx = document.createElement("div"); hx.className = "hint"; hx.textContent = fd.hint; f.appendChild(hx); }
      wrap.appendChild(f);
    });

    /* agent picker */
    var agentField = document.createElement("div");
    agentField.className = "app-field full";
    var alabel = document.createElement("label");
    alabel.textContent = "Assigned agent";
    agentField.appendChild(alabel);
    var selWrap = document.createElement("div");
    selWrap.className = "agent-select";
    var current = document.createElement("div");
    current.className = "agent-select-current";
    var hint = document.createElement("div");
    hint.className = "hint";
    hint.textContent = "Shown as the negotiator on the public property page";
    agentField.appendChild(selWrap);
    agentField.appendChild(hint);
    var selectedAgentId = initial ? (Number(initial.agent_id) || null) : null;
    var selectedAgentObj = null;

    function renderAgentPicker() {
      var found = selectedAgentId ? agents.filter(function (a) { return Number(a.id) === selectedAgentId; })[0] : null;
      selectedAgentObj = found || null;
      var txt = found ? found.name : (isEdit && selectedAgentId ? "Agent " + selectedAgentId : "Select an agent…");
      current.innerHTML = found
        ? '<span class="agent-select-placeholder" style="display:flex;align-items:center;gap:8px"><span class="agent-select-avatar">' + esc((found.name || "A")[0].toUpperCase()) + '</span>' + esc(found.name) + "</span>"
        : '<span class="agent-select-placeholder">' + esc(txt) + '</span><span class="agent-select-arrow">▾</span>';
    }

    current.addEventListener("click", function () {
      agentOpen = !agentOpen;
      renderAgentMenu();
    });
    document.addEventListener("click", function (e) {
      if (agentOpen && !e.target.closest(".agent-select")) { agentOpen = false; renderAgentMenu(); }
    });
    var menu = document.createElement("div");
    menu.className = "agent-select-menu";
    menu.style.display = "none";
    selWrap.appendChild(current);
    selWrap.appendChild(menu);

    function filteredAgents() {
      if (!agentQuery) return agents;
      var q2 = agentQuery.toLowerCase();
      return agents.filter(function (a) { return (a.name || "").toLowerCase().indexOf(q2) >= 0; });
    }
    function renderAgentMenu() {
      menu.innerHTML = "";
      if (!agentOpen) { menu.style.display = "none"; return; }
      menu.style.display = "block";
      var search = document.createElement("input");
      search.className = "agent-select-search";
      search.placeholder = "Search agents…";
      search.value = agentQuery;
      search.addEventListener("input", function () { agentQuery = search.value; renderAgentMenu(); });
      search.addEventListener("click", function (e) { e.stopPropagation(); });
      menu.appendChild(search);
      var list = document.createElement("div");
      list.className = "agent-select-list";
      var f = filteredAgents();
      if (f.length === 0) {
        var empty = document.createElement("div");
        empty.className = "agent-select-empty";
        empty.textContent = "No agents found";
        list.appendChild(empty);
      } else {
        f.forEach(function (a) {
          var opt = document.createElement("div");
          opt.className = "agent-select-option" + (selectedAgentId && Number(a.id) === selectedAgentId ? " selected" : "");
          opt.innerHTML = '<span class="agent-select-avatar">' + esc((a.name || "A")[0].toUpperCase()) + '</span>' +
            '<span class="agent-select-meta"><strong>' + esc(a.name) + "</strong><div>" + esc(a.role || "") + "</div></span>";
          opt.addEventListener("click", function () {
            selectedAgentId = Number(a.id);
            agentOpen = false;
            renderAgentMenu();
            renderAgentPicker();
          });
          list.appendChild(opt);
        });
      }
      menu.appendChild(list);
      var clear = document.createElement("button");
      clear.type = "button";
      clear.className = "agent-select-clear";
      clear.textContent = "Clear";
      clear.addEventListener("click", function () {
        selectedAgentId = null; agentOpen = false; renderAgentMenu(); renderAgentPicker();
      });
      menu.appendChild(clear);
    }

    var agentFieldWrap = wrap;
    agentFieldWrap.appendChild(agentField);

    /* Media section */
    var mediaHead = document.createElement("div");
    mediaHead.className = "app-field full";
    var mstrong = document.createElement("strong");
    mstrong.style.cssText = "color:#142121;font-size:14px";
    mstrong.textContent = "Media";
    mediaHead.appendChild(mstrong);
    wrap.appendChild(mediaHead);
    var mediaRows = document.createElement("div");
    mediaRows.className = "app-field full";
    wrap.appendChild(mediaRows);
    var mediaHint = document.createElement("div");
    mediaHint.className = "app-field full";
    mediaHint.id = "media-upload-hint";
    wrap.appendChild(mediaHint);

    function renderMedia() {
      mediaRows.innerHTML = "";
      media.forEach(function (m, idx) {
        var row = document.createElement("div");
        row.style.cssText = "display:flex;align-items:center;gap:8px;padding:4px 0";
        if (m.kind === "video") {
          var v = document.createElement("span");
          v.style.cssText = "flex:0 0 56px;font-size:10px;letter-spacing:0.5px;text-align:center;color:#9399a4";
          v.textContent = "VIDEO";
          row.appendChild(v);
        } else {
          var img = document.createElement("img");
          img.src = m.url;
          img.style.cssText = "width:56px;height:40px;object-fit:cover;border-radius:4px";
          img.addEventListener("error", function () { img.style.display = "none"; });
          row.appendChild(img);
        }
        var kindSel = document.createElement("select");
        kindSel.style.cssText = "border:1px solid #e1e8ed;border-radius:6px;font-size:12px;padding:4px 6px";
        ["image", "video", "floorplan", "brochure"].forEach(function (k) {
          var o = document.createElement("option");
          o.value = k; o.textContent = k;
          kindSel.appendChild(o);
        });
        kindSel.value = m.kind || "image";
        kindSel.addEventListener("change", function () { m.kind = kindSel.value; });
        row.appendChild(kindSel);
        var name = document.createElement("span");
        name.style.cssText = "color:#35373c;flex:1;font-size:13px;overflow:hidden;textOverflow:ellipsis;whiteSpace:nowrap";
        name.textContent = m.url;
        name.title = m.url;
        row.appendChild(name);
        var del = document.createElement("button");
        del.type = "button";
        del.className = "app-btn danger sm";
        del.textContent = "×";
        del.addEventListener("click", function () { media.splice(idx, 1); renderMedia(); });
        row.appendChild(del);
        mediaRows.appendChild(row);
      });
    }

    function doUpload(input, kind) {
      var file = input.files[0];
      if (!file) return;
      uploading.total += 1;
      mediaHint.textContent = "Uploading " + uploading.done + "/" + uploading.total + "…";
      var fd = new FormData();
      fd.append("file", file);
      api("/api/admin/upload", { method: "POST", body: fd }).then(function (res) {
        uploading.done += 1;
        if (res.ok) {
          media.push({ kind: kind, url: res.data.url });
          renderMedia();
        } else {
          window.alert(res.data.error || "Upload failed");
        }
        if (uploading.total > 0 && uploading.done >= uploading.total) { uploading = { done: 0, total: 0 }; }
        mediaHint.textContent = "";
      }).catch(function () {
        uploading.done += 1;
        window.alert("Upload failed");
        mediaHint.textContent = "";
      });
    }

    var addImg = document.createElement("button");
    addImg.type = "button";
    addImg.className = "app-btn ghost sm";
    addImg.textContent = "+ Add images";
    var imgInput = document.createElement("input");
    imgInput.type = "file";
    imgInput.accept = "image/*";
    imgInput.style.display = "none";
    imgInput.addEventListener("change", function () { doUpload(imgInput, "image"); imgInput.value = ""; });
    addImg.addEventListener("click", function () { imgInput.click(); });
    var addVid = document.createElement("button");
    addVid.type = "button";
    addVid.className = "app-btn ghost sm";
    addVid.textContent = "+ Add video";
    var vidInput = document.createElement("input");
    vidInput.type = "file";
    vidInput.accept = "video/*";
    vidInput.style.display = "none";
    vidInput.addEventListener("change", function () { doUpload(vidInput, "video"); vidInput.value = ""; });
    addVid.addEventListener("click", function () { vidInput.click(); });
    var btnWrap = document.createElement("div");
    btnWrap.className = "app-field full";
    var bwrap = document.createElement("div");
    bwrap.style.cssText = "display:flex;gap:10px";
    bwrap.appendChild(addImg);
    bwrap.appendChild(addVid);
    btnWrap.appendChild(bwrap);
    btnWrap.appendChild(imgInput);
    btnWrap.appendChild(vidInput);
    wrap.appendChild(btnWrap);
    renderMedia();

    /* Amenities section */
    var amHead = document.createElement("div");
    amHead.className = "app-field full";
    var astrong = document.createElement("strong");
    astrong.style.cssText = "color:#142121;font-size:14px";
    astrong.textContent = "Amenities";
    amHead.appendChild(astrong);
    wrap.appendChild(amHead);
    var chipList = document.createElement("div");
    chipList.className = "app-field full";
    wrap.appendChild(chipList);
    var amCount = document.createElement("div");
    amCount.className = "app-field full";
    wrap.appendChild(amCount);
    var addRow = document.createElement("div");
    addRow.className = "app-field full";
    wrap.appendChild(addRow);

    function renderChips() {
      chipList.innerHTML = "";
      var wrapd = document.createElement("div");
      wrapd.className = "app-chip-list";
      amenityList.forEach(function (a) {
        var on = selectedAmenities.indexOf(a) >= 0;
        var chip = document.createElement("button");
        chip.type = "button";
        chip.className = "app-chip" + (on ? " active" : "");
        chip.title = "Delete " + a;
        var label = document.createElement("span");
        label.className = "app-chip-label";
        label.textContent = a;
        chip.appendChild(label);
        var x = document.createElement("span");
        x.className = "app-chip-x";
        x.innerHTML = '<svg viewBox="0 0 10 10" width="10" height="10" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 1L9 9M9 1L1 9"/></svg>';
        x.addEventListener("click", function (e) {
          e.stopPropagation();
          var idx = amenityList.indexOf(a);
          if (idx >= 0) amenityList.splice(idx, 1);
          var si = selectedAmenities.indexOf(a);
          if (si >= 0) selectedAmenities.splice(si, 1);
          renderChips();
        });
        chip.appendChild(x);
        chip.addEventListener("click", function () {
          var si = selectedAmenities.indexOf(a);
          if (si >= 0) selectedAmenities.splice(si, 1);
          else selectedAmenities.push(a);
          renderChips();
        });
        wrapd.appendChild(chip);
      });
      chipList.appendChild(wrapd);
      amCount.innerHTML = '<div class="hint">' + selectedAmenities.length + " amenit" + (selectedAmenities.length === 1 ? "y" : "ies") + " selected</div>";
    }

    var addInput = document.createElement("input");
    addInput.type = "text";
    addInput.placeholder = "Add a new amenity (e.g. Smart Home)";
    addInput.style.cssText = "flex:1;min-width:160px";
    var addBtn = document.createElement("button");
    addBtn.type = "button";
    addBtn.className = "app-btn ghost sm";
    addBtn.textContent = "Add";
    addBtn.addEventListener("click", function () {
      var name = addInput.value.trim();
      if (!name) return;
      api("/api/admin/amenities", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ name: name }) }).then(function () {
        amenityList.push(name);
        if (selectedAmenities.indexOf(name) < 0) selectedAmenities.push(name);
        addInput.value = "";
        renderChips();
      });
    });
    var addWrap = document.createElement("div");
    addWrap.style.cssText = "display:flex;gap:8px;align-items:center";
    addWrap.appendChild(addInput);
    addWrap.appendChild(addBtn);
    addRow.appendChild(addWrap);
    renderChips();

    /* actions */
    var actions = document.createElement("div");
    actions.className = "form-actions full";
    var cancel = document.createElement("button");
    cancel.type = "button";
    cancel.className = "app-btn ghost";
    cancel.textContent = "Cancel";
    cancel.addEventListener("click", onDone);
    var save = document.createElement("button");
    save.type = "submit";
    save.className = "app-btn";
    save.textContent = "Save property";
    actions.appendChild(cancel);
    actions.appendChild(save);
    wrap.appendChild(actions);
    root.appendChild(wrap);
    container.appendChild(root);
    renderAgentPicker();
    api("/api/admin/agents").then(function (res) {
      agents = (res.data.items || []).filter(function (a) { return Number(a.published) === 1; });
      renderAgentPicker();
    });
    api("/api/admin/amenities").then(function (res) {
      amenityList = (res.data.items || []).map(function (a) { return a.name; });
      renderChips();
    });

    function coerce() {
      var body = {};
      FIELDS.forEach(function (fd) {
        var v = form[fd.key];
        if (fd.type === "checkbox") { body[fd.key] = v ? 1 : 0; }
        else if (fd.type === "number") { body[fd.key] = v === "" || v === null ? 0 : Number(v); }
        else body[fd.key] = v;
      });
      body.agent_id = selectedAgentId;
      body.amenities = selectedAmenities;
      body.media = media.filter(function (m) { return m.url.trim(); });
      return body;
    }

    var frm = document.createElement("form");
    frm.appendChild(wrap);
    container.innerHTML = "";
    container.appendChild(root);
    root.innerHTML = "";
    root.appendChild(frm);
    frm.addEventListener("submit", function (e) {
      e.preventDefault();
      save.disabled = true;
      var url = "/api/admin/properties" + (isEdit ? "?id=" + initial.id : "");
      var method = isEdit ? "PUT" : "POST";
      api(url, { method: method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(coerce()) })
        .then(function (res) {
          save.disabled = false;
          if (!res.ok) { window.alert(res.data.error || "Save failed"); return; }
          onDone();
        })
        .catch(function () { save.disabled = false; window.alert("Save failed"); });
    });
  }

  /* ------------------------------ Users ------------------------------ */

  function initUsers(panel) {
    var items = null, editing = null, creating = false;
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);

    function load() {
      api("/api/admin/users").then(function (res) {
        items = res.data.items || [];
        render();
      }).catch(function () { items = []; render(); });
    }
    function remove(row) {
      if (!window.confirm('Delete "' + (row.name || row.id) + '"?')) return;
      api("/api/admin/users/" + row.id, { method: "DELETE" }).then(function () { load(); });
    }
    function save(state, saveBtn) {
      saveBtn.disabled = true;
      var url = "/api/admin/users" + (editing ? "/" + editing.id : "");
      var method = editing ? "PUT" : "POST";
      api(url, { method: method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(state) })
        .then(function (res) {
          saveBtn.disabled = false;
          if (!res.ok) { showToast(res.data.error || "Save failed"); return; }
          showToast(editing ? "User updated" : "User created");
          editing = null; creating = false;
          load();
        });
    }
    function render() {
      if (creating || editing) {
        card.innerHTML = "";
        var userFields = [
          { key: "name", label: "Full name", required: true, full: true },
          { key: "email", label: "Email", required: true, full: true },
          { key: "phone", label: "Phone" },
          { key: "role", label: "Role", type: "select", options: ["user", "agent", "admin"] },
          { key: "password", label: editing ? "New password (leave blank to keep)" : "Password", full: true },
          { key: "is_active", label: "Account active", type: "checkbox", hint: "Enabled" },
        ];
        var init = {};
        userFields.forEach(function (fd) {
          init[fd.key] = editing ? editing[fd.key] : (fd.type === "checkbox" ? 1 : "");
        });
        if (!editing) init.password = "";
        formPageCard(card, editing ? "Edit User" : "New User", "Users", userFields, init, save, function () {
          editing = null; creating = false; render();
        });
        return;
      }
      var head = '<div class="app-card-head"><div><h2>Users</h2><p class="app-card-sub">' + (items ? items.length : 0) + " accounts</p></div>" +
        '<button type="button" class="app-btn" data-add>+ Add user</button></div>';
      card.innerHTML = head + (items === null ? '<p class="app-empty">Loading…</p>' : items.length === 0 ? '<p class="app-empty">No users.</p>' :
        '<div style="overflow-x:auto"><table class="app-table"><thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last login</th><th></th></tr></thead><tbody>' +
        items.map(function (u) {
          var roleBadge = u.role === "admin" ? '<span class="app-badge" style="background:#e3f2fd;color:#075985">' + esc(u.role) + "</span>" : '<span class="app-badge">' + esc(u.role) + "</span>";
          var statusBadge = Number(u.is_active) === 1 ? '<span class="app-badge active">active</span>' : '<span class="app-badge inactive">disabled</span>';
          var toggle = Number(u.is_active) === 1 ? "Disable" : "Enable";
          return "<tr><td><strong>" + esc(u.name) + "</strong><div style=\"font-size:12px;color:#9399a4\">" + esc(u.email) + (u.phone ? " · " + esc(u.phone) : "") + "</div></td>" +
            "<td>" + roleBadge + "</td><td>" + statusBadge + "</td><td>" + esc(u.last_login_at ? fmtDate(u.last_login_at) : "—") + "</td>" +
            '<td><div class="row-actions">' +
            '<button type="button" class="app-btn ghost sm" data-edit="' + u.id + '">Edit</button>' +
            '<button type="button" class="app-btn ghost sm" data-toggle="' + u.id + '">' + toggle + "</button>" +
            "</div></td></tr>";
        }).join("") + "</tbody></table></div>");
      var add = $("[data-add]", card);
      if (add) add.addEventListener("click", function () { creating = true; render(); });
      $$("[data-edit]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-edit"));
          items.forEach(function (r) { if (Number(r.id) === id) editing = r; });
          render();
        });
      });
      $$("[data-toggle]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-toggle"));
          var row = null;
          items.forEach(function (r) { if (Number(r.id) === id) row = r; });
          if (!row) return;
          api("/api/admin/users/" + id, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ is_active: Number(row.is_active) === 1 ? 0 : 1 }) }).then(function () { load(); });
        });
      });
    }
    load();
  }

  /* ------------------------------ Inquiries ------------------------------ */

  function initInquiries(panel) {
    var items = null, tab = "all", open = null;
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);
    var KINDS = ["all", "agent", "developer", "project", "register", "generic"];

    function load() {
      var url = "/api/admin/inquiries" + (tab !== "all" ? "?kind=" + tab : "");
      api(url).then(function (res) { items = res.data.items || []; render(); })
        .catch(function () { items = []; render(); });
    }
    function setStatus(row, status) {
      api("/api/admin/inquiries", { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id, status: status }) })
        .then(function () { showToast('Marked "' + status + '"'); load(); });
    }
    function remove(row) {
      if (!window.confirm("Delete this inquiry?")) return;
      api("/api/admin/inquiries", { method: "DELETE", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id }) })
        .then(function () { open = null; load(); });
    }
    function openDetail(row) {
      open = row;
      render();
      if (!modalRoot) return;
      openModal("Inquiry", function (body) {
        var key = row.kind || "generic";
        var text = String(row.message || "");
        if (key === "project") {
          try {
            var d = JSON.parse(text);
            var rows = [];
            [["Project", d.project || "—"], ["Name", d.name || "—"], ["Email", d.email || "—"], ["Phone", d.phone || "—"], ["Moving time", d.movingTime || "—"], ["Message", d.message || "—"]].forEach(function (r) {
              rows.push('<div class="app-detail-row"><div class="app-detail-label">' + r[0] + '</div><div class="app-detail-value">' + esc(r[1]) + "</div></div>");
            });
            body.innerHTML = rows.join("") + detailActions(row);
          } catch (e) { body.innerHTML = '<p class="app-empty">' + esc(text) + "</p>" + detailActions(row); }
        } else if (key === "register") {
          try {
            var rd = JSON.parse(text);
            var rrows = [];
            Object.keys(rd).forEach(function (k) {
              rrows.push('<div class="app-detail-row"><div class="app-detail-label">' + esc(k) + '</div><div class="app-detail-value">' + esc(rd[k]) + "</div></div>");
            });
            body.innerHTML = rrows.join("") + detailActions(row);
          } catch (e) { body.innerHTML = '<p class="app-empty">' + esc(text) + "</p>" + detailActions(row); }
        } else {
          var msg = text;
          if (key === "agent") {
            try {
              var ad = JSON.parse(text);
              if (ad.agent_name) msg = "Agent: " + ad.agent_name + "\n" + (ad.message || "");
            } catch (e) {}
          }
          body.innerHTML = detailRows([["Name", row.name || "—"], ["Email", row.email || "—"], ["Phone", row.phone || "—"], ["Message", msg]]) + detailActions(row);
        }
      });
    }
    function detailRows(rows) {
      return rows.map(function (r) {
        return '<div class="app-detail-row"><div class="app-detail-label">' + r[0] + '</div><div class="app-detail-value">' + (r[1] || "—") + "</div></div>";
      }).join("");
    }
    function detailActions(row) {
      var opts = ["new", "contacted", "closed"].map(function (s) {
        return '<option value="' + s + '"' + (row.status === s ? " selected" : "") + ">" + s + "</option>";
      }).join("");
      return '<div class="app-detail-actions">' +
        '<select data-inq-status style="border:1px solid #e1e8ed;border-radius:6px;font-size:12px;padding:6px 10px">' + opts + "</select>" +
        '<button type="button" class="app-btn danger sm" data-inq-del>Delete</button></div>';
    }
    function render() {
      var head = '<div class="app-card-head"><div><h2>Inquiries</h2><p class="app-card-sub">' + (items ? items.length : 0) + " messages</p></div></div>";
      var tabs = '<div class="app-tabs">' + KINDS.map(function (k) {
        return '<button type="button" class="app-tab' + (tab === k ? " active" : "") + '" data-kind="' + k + '">' + k + "</button>";
      }).join("") + "</div>";
      card.innerHTML = head + tabs + (items === null ? '<p class="app-empty">Loading…</p>' :
        items.length === 0 ? '<p class="app-empty">No inquiries.</p>' :
        '<div style="overflow-x:auto"><table class="app-table"><thead><tr><th>Name</th><th>Kind</th><th>Message</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>' +
        items.map(function (i) {
          return "<tr><td><strong>" + esc(i.name) + "</strong><div style=\"font-size:12px;color:#9399a4\">" + esc(i.email) + "</div></td>" +
            "<td>" + esc(i.kind) + "</td><td>" + esc(String(i.message || "").slice(0, 50)) + "</td>" +
            '<td><span class="app-badge ' + esc(i.status) + '">' + esc(i.status) + "</span></td>" +
            "<td>" + esc(fmtDate(i.created_at)) + "</td>" +
            '<td><button type="button" class="app-btn ghost sm" data-open="' + i.id + '">View</button></td></tr>';
        }).join("") + "</tbody></table></div>");
      $$("[data-kind]", card).forEach(function (b) {
        b.addEventListener("click", function () { tab = b.getAttribute("data-kind"); open = null; load(); });
      });
      $$("[data-open]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-open"));
          var row = null;
          (items || []).forEach(function (r) { if (Number(r.id) === id) row = r; });
          if (row) openDetail(row);
        });
      });
      if (open) {
        var ss = $("[data-inq-status]");
        if (ss) ss.addEventListener("change", function () { setStatus(open, ss.value); closeModal(); });
        var dd = $("[data-inq-del]");
        if (dd) dd.addEventListener("click", function () { remove(open); closeModal(); });
      }
    }
    load();
  }

  /* ------------------------------ Listings (kind=listing) ------------------------------ */

  function initListings(panel) {
    var items = null, open = null;
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);
    function load() {
      api("/api/admin/inquiries?kind=listing").then(function (res) { items = res.data.items || []; render(); })
        .catch(function () { items = []; render(); });
    }
    function setStatus(row, status) {
      api("/api/admin/inquiries", { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id, status: status }) })
        .then(function () { showToast('Listing marked "' + status + '"'); load(); });
    }
    function remove(row) {
      if (!window.confirm("Delete this listing request?")) return;
      api("/api/admin/inquiries", { method: "DELETE", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id }) })
        .then(function () { open = null; load(); });
    }
    function openDetail(row) {
      open = row;
      render();
      var payload = parseListingPayload(String(row.message || ""));
      var map = [
        ["Name", "name"], ["Email", "email"], ["Phone", "phone"],
        ["Transaction", "transaction"], ["Property Type", "property_type"], ["Community / Area", "community"],
        ["Bedrooms", "bedrooms"], ["Bathrooms", "bathrooms"], ["Size (sq ft)", "size_sqft"],
        ["Expected Price (AED)", "expected_price"], ["Ownership Status", "ownership"], ["Message", "message"],
      ];
      var html;
      if (payload) {
        html = map.map(function (m) {
          return '<div class="app-detail-row"><div class="app-detail-label">' + m[0] + '</div><div class="app-detail-value">' + esc(payload[m[1]] || "—") + "</div></div>";
        }).join("");
      } else {
        html = '<div class="app-detail-row"><div class="app-detail-label">Kind</div><div class="app-detail-value">' + esc(row.kind) + "</div></div>" +
          '<div class="app-detail-row"><div class="app-detail-label">Property</div><div class="app-detail-value">' + esc((row.property_slug || row.property_ref) || "—") + "</div></div>" +
          '<div class="app-detail-row"><div class="app-detail-label">Message</div><div class="app-detail-value">' + esc(row.message || "") + "</div></div>";
      }
      html += '<div class="app-detail-group-label">Details</div>' +
        '<div class="app-detail-row"><div class="app-detail-label">Received</div><div class="app-detail-value">' + esc(fmtDate(row.created_at)) + "</div></div>" +
        '<div class="app-detail-row"><div class="app-detail-label">User</div><div class="app-detail-value">' + esc(row.user_name || row.user_email || "Guest") + "</div></div>";
      html += '<div class="app-detail-actions">' +
        '<select data-list-status style="border:1px solid #e1e8ed;border-radius:6px;font-size:12px;padding:6px 10px">' +
        ["new", "contacted", "closed"].map(function (s) { return '<option value="' + s + '"' + (row.status === s ? " selected" : "") + ">" + s + "</option>"; }).join("") +
        '</select><button type="button" class="app-btn danger sm" data-list-del>Delete</button></div>';
      openModal("Listing", function (body) { body.innerHTML = html; });
      var ss = $("[data-list-status]");
      if (ss) ss.addEventListener("change", function () { setStatus(row, ss.value); closeModal(); });
      var dd = $("[data-list-del]");
      if (dd) dd.addEventListener("click", function () { remove(row); closeModal(); });
    }
    function render() {
      var head = '<div class="app-card-head"><div><h2>Listings</h2><p class="app-card-sub">' + (items ? items.length : 0) + ' property submissions</p></div></div>';
      card.innerHTML = head + (items === null ? '<p class="app-empty">Loading…</p>' :
        items.length === 0 ? '<p class="app-empty">No property listings yet. Submissions from the "List Your Property" form appear here.</p>' :
        '<div style="overflow-x:auto"><table class="app-table"><thead><tr><th>Name</th><th>Property</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>' +
        items.map(function (i) {
          var payload = parseListingPayload(String(i.message || ""));
          var prop = payload ? (payload.community || payload.property_type || "Listing") : (i.property_slug || i.property_ref || "Listing");
          return "<tr><td><strong>" + esc(i.name) + "</strong><div style=\"font-size:12px;color:#9399a4\">" + esc(i.email) + "</div></td>" +
            "<td>" + esc(prop) + "</td>" +
            '<td><span class="app-badge ' + esc(i.status) + '">' + esc(i.status) + "</span></td>" +
            "<td>" + esc(fmtDate(i.created_at)) + "</td>" +
            '<td><button type="button" class="app-btn ghost sm" data-open="' + i.id + '">View</button></td></tr>';
        }).join("") + "</tbody></table></div>");
      $$("[data-open]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-open"));
          (items || []).forEach(function (r) { if (Number(r.id) === id) openDetail(r); });
        });
      });
    }
    load();
  }

  /* ------------------------------ Viewings ------------------------------ */

  function initViewings(panel) {
    var items = null, open = null;
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);
    function load() {
      api("/api/admin/viewings").then(function (res) { items = res.data.items || []; render(); })
        .catch(function () { items = []; render(); });
    }
    function remove(row) {
      if (!window.confirm("Delete this viewing?")) return;
      api("/api/admin/viewings/" + row.id, { method: "DELETE" }).then(function () { open = null; load(); });
    }
    function render() {
      var head = '<div class="app-card-head"><div><h2>Viewings</h2><p class="app-card-sub">' + (items ? items.length : 0) + " scheduled viewings</p></div></div>";
      card.innerHTML = head + (items === null ? '<p class="app-empty">Loading…</p>' :
        items.length === 0 ? '<p class="app-empty">No viewings yet.</p>' :
        '<div style="overflow-x:auto"><table class="app-table"><thead><tr><th>User</th><th>Property</th><th>Scheduled</th><th></th></tr></thead><tbody>' +
        items.map(function (v) {
          return "<tr><td><strong>" + esc(v.user_name || v.user_email || "—") + "</strong></td>" +
            "<td>" + esc(v.property_title || "—") + "</td>" +
            "<td>" + esc(v.scheduled_at ? new Date(v.scheduled_at).toLocaleString("en-GB") : "—") + "</td>" +
            '<td><button type="button" class="app-btn danger sm" data-del="' + v.id + '">Delete</button></td></tr>';
        }).join("") + "</tbody></table></div>");
      $$("[data-del]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-del"));
          (items || []).forEach(function (r) { if (Number(r.id) === id) remove(r); });
        });
      });
    }
    load();
  }

  /* ------------------------------ Categories / Amenities ------------------------------ */

  var CAT_ICONS = ["M3 12 9 6l6 6m0 0 6-6m-6 6v6", "M3 12 9 6l6 6 6-6M9 6v6m0 0v6", "M4 4h7l7 7v9h-7m0 0v-7H7v7", "M12 3l9 5-9 5-9-5 9-5Z", "M4 21V8m16 13V8m-8 13V3", "M6 21v-8m4 8v-6m4 6v-8m4 8V9", "M12 2l8 4-8 4-8-4 8-4Z", "M5 12h14M12 5v14"];

  function initCategories(panel, endpoint, title) {
    var items = null, editing = null, creating = false;
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);
    function load() {
      api("/api/admin/" + endpoint).then(function (res) { items = res.data.items || []; render(); })
        .catch(function () { items = []; render(); });
    }
    function save(state, saveBtn) {
      saveBtn.disabled = true;
      var body = { name: state.name, slug: state.slug, type: state.type, sort: Number(state.sort) || 0 };
      var url = "/api/admin/" + endpoint + (editing ? "/" + editing.id : "");
      api(url, { method: editing ? "PUT" : "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) })
        .then(function (res) {
          saveBtn.disabled = false;
          if (!res.ok) { showToast(res.data.error || "Save failed"); return; }
          showToast(editing ? "Updated" : "Created");
          editing = null; creating = false; load();
        });
    }
    function render() {
      if (creating || editing) {
        card.innerHTML = "";
        var fields = [
          { key: "name", label: "Name", full: true },
          { key: "slug", label: "Slug", full: true },
          { key: "type", label: "Type" },
          { key: "sort", label: "Sort order", type: "number" },
        ];
        var init = {};
        fields.forEach(function (fd) {
          init[fd.key] = editing ? (editing[fd.key] === null || editing[fd.key] === undefined ? "" : editing[fd.key]) : "";
        });
        formPageCard(card, editing ? "Edit category" : "Add category", title, fields, init, save, function () {
          editing = null; creating = false; render();
        });
        return;
      }
      var head = '<div class="app-card-head"><div><h2>' + esc(title) + '</h2><p class="app-card-sub">' + (items ? items.length : 0) + " entries</p></div>" +
        '<button type="button" class="app-btn" data-add>+ Add</button></div>';
      card.innerHTML = head + (items === null ? '<p class="app-empty">Loading…</p>' : items.length === 0 ? '<p class="app-empty">No records.</p>' : resourceTable(items, CAT_COLS));
      var add = $("[data-add]", card);
      if (add) add.addEventListener("click", function () { creating = true; render(); });
      $$("[data-edit]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-edit"));
          (items || []).forEach(function (r) { if (Number(r.id) === id) editing = r; });
          render();
        });
      });
      $$("[data-del]", card).forEach(function (b) {
        b.addEventListener("click", function () {
          var id = Number(b.getAttribute("data-del"));
          (items || []).forEach(function (r) {
            if (Number(r.id) === id && window.confirm('Delete "' + (r.name || r.title || r.id) + '"?')) {
              api("/api/admin/" + endpoint + "/" + id, { method: "DELETE" }).then(function () { load(); });
            }
          });
        });
      });
    }
    load();
  }

  /* ------------------------------ KV managers ------------------------------ */

  function kvManager(panel, endpoint, title, fields, heading) {
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);
    card.innerHTML = '<p class="app-empty">Loading…</p>';
    api("/api/admin/" + endpoint).then(function (res) {
      var rows = res.data.items || [];
      var init = {};
      fields.forEach(function (fd) { init[fd.key] = ""; });
      rows.forEach(function (r) {
        if (Object.prototype.hasOwnProperty.call(init, r.key)) {
          init[r.key] = String(r.value === null || r.value === undefined ? "" : r.value);
        }
      });
      var head = document.createElement("div");
      head.className = "app-card-head";
      var h2 = document.createElement("h2");
      h2.textContent = title;
      var sub = document.createElement("p");
      sub.className = "app-card-sub";
      sub.textContent = heading || "";
      head.appendChild(h2);
      head.appendChild(sub);
      card.innerHTML = "";
      card.appendChild(head);
      buildForm(card, fields, init, {
        onSave: function (state, saveBtn) {
          saveBtn.disabled = true;
          var items = fields.map(function (fd) {
            var v = state[fd.key];
            return { key: fd.key, value: String(v === null || v === undefined ? "" : v) };
          });
          api("/api/admin/" + endpoint, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ items: items }) })
            .then(function (res) {
              saveBtn.disabled = false;
              if (!res.ok) { showToast(res.data.error || "Save failed"); return; }
              showToast("Saved");
            });
        },
      });
    }).catch(function () {
      card.innerHTML = '<p class="app-empty">Could not load.</p>';
    });
  }

  var COUNTRIES = ["Afghanistan", "Albania", "Algeria", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahrain", "Bangladesh", "Belarus", "Belgium", "Brazil", "Bulgaria", "Cambodia", "Cameroon", "Canada", "Chile", "China", "Colombia", "Costa Rica", "Croatia", "Cyprus", "Czech Republic", "Denmark", "Dominican Republic", "Ecuador", "Egypt", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Georgia", "Germany", "Ghana", "Greece", "Hungary", "Iceland", "India", "Indonesia", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kuwait", "Kyrgyzstan", "Latvia", "Lebanon", "Lithuania", "Luxembourg", "Malaysia", "Maldives", "Malta", "Mauritius", "Mexico", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nepal", "Netherlands", "New Zealand", "Nigeria", "North Macedonia", "Norway", "Oman", "Pakistan", "Palestine", "Panama", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saudi Arabia", "Serbia", "Seychelles", "Singapore", "Slovakia", "Slovenia", "South Africa", "South Korea", "Spain", "Sri Lanka", "Sudan", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Tunisia", "Turkey", "Turkmenistan", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vietnam", "Yemen", "Zambia", "Zimbabwe"];

  function initHomepage(panel) {
    kvManager(panel, "homepage", "Homepage Content", [
      { key: "hero_title", label: "Headline", full: true },
      { key: "hero_subtitle", label: "Sub-headline", type: "textarea", full: true },
      { key: "announcement_bar", label: "Announcement bar", full: true },
      { key: "stats_heading", label: "Stats section heading", full: true },
      { key: "featured_heading", label: "Featured projects heading", full: true },
    ], "Edit the content shown on the public homepage. Leave a field empty to keep the current website text.");
  }

  function initAbout(panel) {
    kvManager(panel, "about", "About Us", [
      { key: "hero_title", label: "Main paragraph", type: "textarea", full: true },
      { key: "intro", label: "Intro text (HTML allowed)", type: "textarea", full: true },
    ], "Edit the content shown on the public About page. Leave a field empty to keep the current website text.");
  }

  function initContact(panel) {
    kvManager(panel, "contact", "Contact Us", [
      { key: "country", label: "Country", type: "select", options: COUNTRIES },
      { key: "phone", label: "Phone", full: true },
      { key: "email", label: "Email", full: true },
      { key: "whatsapp", label: "WhatsApp", full: true },
      { key: "address", label: "Address", type: "textarea", full: true },
      { key: "office_hours", label: "Office hours", full: true },
    ], "Contact details shown on the public site.");
  }

  /* ------------------------------ Project Details ------------------------------ */

  function initProjectDetails(panel) {
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);

    function toLines(arr, pick) {
      return (arr || []).map(pick).filter(Boolean).join("\n");
    }

    function load() {
      card.innerHTML = '<p class="app-empty">Loading…</p>';
      api("/api/admin/project-details").then(function (res) {
        var list = res.data.items || [];
        var head = '<div class="app-card-head"><div><h2>Project Details</h2><p class="app-card-sub">' + list.length + ' projects with rich detail pages</p></div></div>';
        card.innerHTML = head + (list.length === 0 ? '<p class="app-empty">No project details yet.</p>' :
          '<div style="overflow-x:auto"><table class="app-table"><thead><tr><th>Project</th><th>Developer</th><th>Location</th><th>Completion</th><th>Updated</th><th></th></tr></thead><tbody>' +
          list.map(function (row) {
            return '<tr><td><strong>' + esc(row.title || row.slug) + '</strong><div style="font-size:12px;color:#9399a4">' + esc(row.slug) + "</div></td>" +
              "<td>" + esc(row.developer || "—") + "</td>" +
              "<td>" + esc(row.display_address || "—") + "</td>" +
              "<td>" + esc(row.completion_year || "—") + "</td>" +
              "<td>" + esc(fmtDate(row.updated_at)) + "</td>" +
              '<td><div class="row-actions">' +
              '<button type="button" class="app-btn ghost sm" data-edit="' + esc(row.slug) + '">Edit</button>' +
              '<a class="app-btn ghost sm" href="/new-projects/' + esc(row.slug) + '/" target="_blank">View</a>' +
              "</div></td></tr>";
          }).join("") + "</tbody></table></div>");
        $$("[data-edit]", card).forEach(function (b) {
          b.addEventListener("click", function () {
            openEdit(b.getAttribute("data-edit"));
          });
        });
      }).catch(function () {
        card.innerHTML = '<p class="app-empty">Could not load projects.</p>';
      });
    }

    function openEdit(slug) {
      api("/api/admin/project-details?slug=" + encodeURIComponent(slug)).then(function (res) {
        if (!res.ok) { showToast(res.data.error || "Could not load project"); return; }
        var data = res.data.item && res.data.item.data ? res.data.item.data : {};
        editForm(slug, data);
      }).catch(function () { showToast("Could not load project"); });
    }

    function editForm(slug, data) {
      card.innerHTML = "";
      var back = document.createElement("div");
      back.className = "app-card-head";
      var h2 = document.createElement("h2");
      h2.textContent = data.title || slug;
      var sub = document.createElement("p");
      sub.className = "app-card-sub";
      sub.textContent = slug + " — saved fields drive the public project page. Fields left in the raw import keep their scraped values.";
      var bb = document.createElement("button");
      bb.type = "button";
      bb.className = "app-btn ghost sm form-page-back";
      bb.textContent = "← Back to Project Details";
      bb.addEventListener("click", load);
      back.appendChild(h2);
      back.appendChild(sub);
      back.appendChild(bb);
      card.appendChild(back);

      var FIELDS = [
        { key: "about", label: "About the project (HTML allowed)", type: "textarea", full: true },
        { key: "display_price", label: "Display price (e.g. 1.96M)", full: true },
        { key: "completion_year", label: "Completion year", full: true },
        { key: "payment_plan_text", label: "Payment plan text (e.g. 80/20)", full: true },
        { key: "gallery", label: "Gallery images (one URL per line)", type: "textarea", full: true },
        { key: "amenities", label: "Amenities (one per line: Name|Image URL)", type: "textarea", full: true },
        { key: "floorplans", label: "Floor plans (one per line: Title|Image URL)", type: "textarea", full: true },
        { key: "usp_heading", label: "USP heading", full: true },
        { key: "usp_title", label: "USP title", full: true },
        { key: "usp_description", label: "USP description (HTML allowed)", type: "textarea", full: true },
        { key: "usp_image", label: "USP image URL", full: true },
        { key: "loc_heading", label: "Location heading", full: true },
        { key: "loc_title", label: "Location title", full: true },
        { key: "loc_description", label: "Location description (HTML allowed)", type: "textarea", full: true },
        { key: "loc_image", label: "Location image URL", full: true },
        { key: "brochure_pdf", label: "Brochure PDF URL", full: true },
        { key: "brochure_cover", label: "Brochure cover image URL", full: true },
        { key: "faqs", label: "FAQ (one per line: Question|Answer)", type: "textarea", full: true },
      ];
      var init = {
        about: String(data.about || ""),
        display_price: String(data.display_price || ""),
        completion_year: String(data.completion_year || ""),
        payment_plan_text: String(data.payment_plan_text || ""),
        gallery: toLines(data.media_images, function (i) { return i ? i.url : ""; }),
        amenities: toLines(data.amenities, function (a) {
          return a && a.image && a.image.url ? a.text + "|" + a.image.url : (a ? String(a.text || "") : "");
        }),
        floorplans: toLines(data.floor_plans, function (p) {
          return p && p.media && p.media.url ? p.title + "|" + p.media.url : (p ? String(p.title || "") : "");
        }),
        usp_heading: String((data.characteristics_module || {}).heading || ""),
        usp_title: String((data.characteristics_module || {}).title || ""),
        usp_description: String((data.characteristics_module || {}).description || ""),
        usp_image: String(((data.characteristics_module || {}).image || {}).url || ""),
        loc_heading: String((data.location_tile || {}).heading || ""),
        loc_title: String((data.location_tile || {}).title || ""),
        loc_description: String((data.location_tile || {}).description || ""),
        loc_image: String(((data.location_tile || {}).image || {}).url || ""),
        brochure_pdf: String(((data.brochure || {}).file || {}).url || ""),
        brochure_cover: String(((data.brochure || {}).image || {}).url || ""),
        faqs: toLines(data.more_info, function (f) {
          return f ? f.question + "|" + String(f.answer || "").replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim() : "";
        }),
      };
      buildForm(card, FIELDS, init, {
        onSave: function (state, saveBtn) {
          saveBtn.disabled = true;
          var out = {};
          Object.keys(data).forEach(function (k) { out[k] = data[k]; });
          out.about = state.about;
          out.display_price = state.display_price;
          out.completion_year = state.completion_year;
          out.payment_plan_text = state.payment_plan_text;
          out.media_images = String(state.gallery).split("\n").map(function (s) { return s.trim(); }).filter(Boolean).map(function (url) { return { url: url }; });
          out.amenities = String(state.amenities).split("\n").map(function (s) { return s.trim(); }).filter(Boolean).map(function (line) {
            var parts = line.split("|");
            return { text: (parts[0] || "").trim(), image: parts[1] ? { url: parts[1].trim() } : null };
          });
          out.floor_plans = String(state.floorplans).split("\n").map(function (s) { return s.trim(); }).filter(Boolean).map(function (line) {
            var parts = line.split("|");
            return { title: (parts[0] || "").trim(), media: parts[1] ? { url: parts[1].trim() } : null };
          });
          var ch = out.characteristics_module || {};
          out.characteristics_module = {
            heading: state.usp_heading,
            title: state.usp_title,
            description: state.usp_description,
            image: state.usp_image ? { url: state.usp_image } : (ch.image || null),
          };
          var lt = out.location_tile || {};
          out.location_tile = {
            heading: state.loc_heading,
            title: state.loc_title,
            description: state.loc_description,
            image: state.loc_image ? { url: state.loc_image } : (lt.image || null),
          };
          var br = out.brochure || {};
          out.brochure = {
            file: state.brochure_pdf ? { url: state.brochure_pdf } : (br.file || null),
            image: state.brochure_cover ? { url: state.brochure_cover } : (br.image || null),
          };
          out.more_info = String(state.faqs).split("\n").map(function (s) { return s.trim(); }).filter(Boolean).map(function (line) {
            var idx = line.indexOf("|");
            return idx >= 0 ? { question: line.slice(0, idx).trim(), answer: line.slice(idx + 1) } : { question: line, answer: "" };
          });
          api("/api/admin/project-details", { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ slug: slug, data: out }) })
            .then(function (res) {
              saveBtn.disabled = false;
              if (!res.ok) { showToast(res.data.error || "Save failed"); return; }
              showToast("Saved");
              load();
            });
        },
      });
    }

    load();
  }

  /* ------------------------------ More (about / team / careers / contact) ------------------------------ */

  function initMore(panel) {
    var SUBTABS = ["about", "team", "careers", "contact"];
    var tab = "about";
    var card = document.createElement("div");
    card.className = "app-card";
    panel.appendChild(card);
    function render() {
      var head = '<div class="app-card-head"><div><h2>More content</h2></div></div>';
      var tabs = '<div class="app-tabs">' + SUBTABS.map(function (s) {
        return '<button type="button" class="app-tab' + (tab === s ? " active" : "") + '" data-more="' + s + '">' + s + "</button>";
      }).join("") + "</div>";
      card.innerHTML = head + tabs;
      $$("[data-more]", card).forEach(function (b) {
        b.addEventListener("click", function () { tab = b.getAttribute("data-more"); render(); });
      });
      var target = document.createElement("div");
      card.appendChild(target);
      if (tab === "about") kvManager(target, "about", "About Us", [
        { key: "hero_title", label: "Main paragraph", type: "textarea", full: true },
        { key: "intro", label: "Intro text (HTML allowed)", type: "textarea", full: true },
      ], "Edit the content shown on the public About page.");
      else if (tab === "team") initResource(target, "agents", "Team members");
      else if (tab === "careers") initResource(target, "jobs", "Careers");
      else if (tab === "contact") kvManager(target, "contact", "Contact Us", [
        { key: "country", label: "Country", type: "select", options: COUNTRIES },
        { key: "phone", label: "Phone", full: true },
        { key: "email", label: "Email", full: true },
        { key: "whatsapp", label: "WhatsApp", full: true },
        { key: "address", label: "Address", type: "textarea", full: true },
        { key: "office_hours", label: "Office hours", full: true },
      ], "Contact details shown on the public site.");
    }
    render();
  }

  /* ------------------------------ wiring / init ------------------------------ */

  function mountPanels() {
    $$("[data-admin-panel]").forEach(function (panel) {
      var id = panel.getAttribute("data-admin-panel");
      switch (id) {
        case "overview": initStats(panel); break;
        case "properties": initProperties(panel); break;
        case "users": initUsers(panel); break;
        case "inquiries": initInquiries(panel); break;
        case "listings": initListings(panel); break;
        case "viewings": initViewings(panel); break;
        case "services": initResource(panel, "services", "Services"); break;
        case "agents": initResource(panel, "agents", "Agents"); break;
        case "developers": initResource(panel, "developers", "Developers"); break;
        case "communities": initResource(panel, "communities", "Areas"); break;
        case "testimonials": initResource(panel, "testimonials", "Testimonials"); break;
        case "faqs": initResource(panel, "faqs", "FAQs"); break;
        case "media": initResource(panel, "media", "Media library"); break;
        case "jobs": initResource(panel, "jobs", "Careers"); break;
        case "projects": initResource(panel, "projects", "Projects"); break;
        case "categories": initCategories(panel, "categories", "Categories"); break;
        case "amenities": initCategories(panel, "amenities", "Amenities"); break;
        case "homepage": initHomepage(panel); break;
        case "about": initAbout(panel); break;
        case "contact": initContact(panel); break;
        case "project-details": initProjectDetails(panel); break;
        case "more": initMore(panel); break;
      }
    });
  }

  var CAT_COLS = [
    { key: "name", label: "Name", kind: "strong" },
    { key: "slug", label: "Slug", kind: "muted" },
    { key: "type", label: "Type", kind: "dash" },
    { key: "sort", label: "Sort", kind: "dash" },
  ];
  var AM_COLS = [
    { key: "name", label: "Name", kind: "strong" },
  ];

  function initTabs() {
    var btns = $$("[data-admin-tab]");
    if (!btns.length) return;
    var groups = {};
    btns.forEach(function (b) {
      var group = b.getAttribute("data-admin-group") || "main";
      (groups[group] = groups[group] || []).push(b);
    });
    Object.keys(groups).forEach(function (group) {
      groups[group].forEach(function (b) {
        b.addEventListener("click", function () {
          groups[group].forEach(function (x) { x.classList.remove("active"); x.setAttribute("aria-selected", "false"); });
          b.classList.add("active");
          b.setAttribute("aria-selected", "true");
          var id = b.getAttribute("data-admin-tab");
          $$("[data-admin-panel]").forEach(function (p) {
            p.hidden = p.getAttribute("data-admin-panel") !== id;
          });
          document.title = BOOT.user && BOOT.user.name ? BOOT.user.name + " — Admin" : "Admin";
        });
      });
    });
    if (btns[0]) btns[0].click();
  }

  if (modalRoot) modalRoot.addEventListener("click", function (e) {
    if (e.target === modalRoot) closeModal();
  });

  initTabs();
  mountPanels();
})();