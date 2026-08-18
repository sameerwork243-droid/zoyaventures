/* auth.js — login/register form wiring
 * Port of src/components/auth/login-form.tsx + register-form.tsx onSubmit logic. */

(function () {
  "use strict";

  function showError(el, msg) {
    if (!el) return;
    el.textContent = msg;
    el.hidden = false;
  }

  async function submitJSON(url, payload) {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json().catch(function () { return {}; });
    return { ok: res.ok, data: data };
  }

  function wireLogin(form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      var errorEl = form.querySelector("#auth-error") || form.parentElement.querySelector(".auth-error");
      if (errorEl) errorEl.hidden = true;
      var email = form.querySelector("#auth-email").value.trim();
      var password = form.querySelector("#auth-password").value;
      var btn = form.querySelector("button[type=submit]");
      btn.disabled = true;
      btn.textContent = "Signing in…";
      try {
        var res = await submitJSON("/api/auth/login", { email: email, password: password });
        if (!res.ok) {
          showError(errorEl, res.data.error || "Unable to sign in. Please try again.");
          return;
        }
        var role = res.data.user && res.data.user.role;
        window.location.href = (role === "admin" || role === "agent") ? "/admin" : "/dashboard";
      } catch (err) {
        showError(errorEl, "Network error. Please try again.");
      } finally {
        btn.disabled = false;
        btn.textContent = "Continue";
      }
    });
  }

  function wireRegister(form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      var errorEl = form.querySelector("#auth-error") || form.parentElement.querySelector(".auth-error");
      if (errorEl) errorEl.hidden = true;
      var name = form.querySelector("#reg-name").value.trim();
      var email = form.querySelector("#reg-email").value.trim();
      var phone = form.querySelector("#reg-phone").value.trim();
      var password = form.querySelector("#reg-password").value;
      var confirm = form.querySelector("#reg-confirm").value;
      if (password !== confirm) {
        showError(errorEl, "Passwords do not match");
        return;
      }
      var btn = form.querySelector("button[type=submit]");
      btn.disabled = true;
      btn.textContent = "Creating account…";
      try {
        var res = await submitJSON("/api/auth/register", {
          name: name, email: email, phone: phone, password: password,
        });
        if (!res.ok) {
          showError(errorEl, res.data.error || "Unable to create your account. Please try again.");
          return;
        }
        window.location.href = "/dashboard";
      } catch (err) {
        showError(errorEl, "Network error. Please try again.");
      } finally {
        btn.disabled = false;
        btn.textContent = "Create Account";
      }
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector("form[data-auth-form=login]");
    if (form) wireLogin(form);
    form = document.querySelector("form[data-auth-form=register]");
    if (form) wireRegister(form);
  });
})();
