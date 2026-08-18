"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";

export type PortalUser = { id: number; email: string; name: string; phone: string; avatar: string; role: string };

export type PortalNavItem = { key: string; label: string; icon: string; href?: string };
export type PortalNavSection = { label?: string; items: PortalNavItem[] };

export function PortalIcon({ name }: { name: string }) {
  switch (name) {
    case "launch":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z" />
        </svg>
      );
    case "home":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
        </svg>
      );
    case "grid":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M3 5h6v11H3V5zm14 0h4v11h-4V5zm-7 0h4v5h-4V5zm0 6h4v5h-4v-5z" />
        </svg>
      );
    case "heart":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
      );
    case "chat":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z" />
        </svg>
      );
    case "calendar":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z" />
        </svg>
      );
    case "bell":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" />
        </svg>
      );
    case "person":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
        </svg>
      );
    case "settings":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" />
        </svg>
      );
    case "logout":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
        </svg>
      );
    case "search":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M15.5 14h-.79l-.28-.27a6.47 6.47 0 0 0 1.52-4.23C15.95 6.57 13.38 4 10.24 4 7.11 4 4.55 6.57 4.55 9.5S7.11 15 10.24 15c1.61 0 3.1-.58 4.23-1.57l.27.28v.79l5 5L20.49 19l-4.99-5zm-5.26 0C8.07 14 6.11 12.03 6.11 9.5S8.07 5 10.24 5s4.13 1.97 4.13 4.5-1.96 4.5-4.13 4.5z" />
        </svg>
      );
    case "bookmark":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M17 3H7a2 2 0 0 0-2 2v16l7-3 7 3V5a2 2 0 0 0-2-2z" />
        </svg>
      );
    case "building":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M3 21h18v-2H3v2zM7 5h2v2H7V5zm0 4h2v2H7V9zm0 4h2v2H7v-2zm0 4h2v2H7v-2zM13 5h2v2h-2V5zm0 4h2v2h-2V9zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z" />
        </svg>
      );
    case "tag":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z" />
        </svg>
      );
    case "briefcase":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-2 .89-2 2v11c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z" />
        </svg>
      );
    case "users":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
        </svg>
      );
    case "star":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
        </svg>
      );
    case "question":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z" />
        </svg>
      );
    case "image":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" />
        </svg>
      );
    case "document":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
        </svg>
      );
    case "phone":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
        </svg>
      );
    case "map":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z" />
        </svg>
      );
    case "menu":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" />
        </svg>
      );
    case "expand-more":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M16.59 8.59 12 13.17 7.41 8.59 6 10l6 6 6-6z" />
        </svg>
      );
    case "more":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
        </svg>
      );
    case "eye":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
        </svg>
      );
    case "eye-off":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.94 17.94A9.99 9.99 0 0 1 12 20c-5.52 0-10-4.48-10-10 0-1.1.22-2.15.6-3.12l1.46 1.46C2.67 10.25 2 11.15 2 12c0 5.52 4.48 10 10 10 1.1 0 2.15-.22 3.12-.6l1.46 1.46c-.97.53-2.03.9-3.12.9zM13.06 8.06L15.12 10.12C15.32 10.32 15.5 10.56 15.5 10.83c0 1.66-1.34 3-3 3-.27 0-.53-.04-.78-.12L10.94 12.94C11.14 12.74 11.32 12.5 11.32 12.17c0-1.66 1.34-3 3-3 .27 0 .53.04.78.12zM1.1 10.06l1.46-1.46C2.67 7.75 2 8.65 2 9.5c0 1.1.22 2.15.6 3.12L1.1 10.06zM12 7c-2.76 0-5 2.24-5 5 0 1.1.22 2.15.6 3.12l1.46-1.46C8.67 12.25 8 11.35 8 10.5c0-1.1-.22-2.15-.6-3.12L7.06 7.06C7.4 6.7 7.73 6.5 8 6.5c1.66 0 3 1.34 3 3 0 .27.04.53.12.78l1.46-1.46C12.33 8.75 12 8.15 12 7.5c0-.27-.04-.53-.12-.78L13.06 8.06z" />
        </svg>
      );
    default:
      return null;
  }
}

export function PortalFooter() {
  return (
    <footer className="portal-footer">
      <div className="portal-footer-inner">
        <div className="portal-copy">
          © 2024, Zoya Ventures Real Estate
          <Link href="/privacy-policy/">Privacy Policy</Link>
        </div>
        <div className="portal-siteby">
          Site by <span>Starberry</span>
        </div>
      </div>
    </footer>
  );
}

export function PortalShell({
  user,
  title,
  sections,
  active,
  onNav,
  children,
}: {
  user: PortalUser;
  title: string;
  sections: PortalNavSection[];
  active: string;
  onNav: (key: string) => void;
  children: React.ReactNode;
}) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [busy, setBusy] = useState(false);

  async function logout() {
    if (busy) return;
    setBusy(true);
    await fetch("/api/auth/logout", { method: "POST" });
    setMenuOpen(false);
    router.push("/");
    router.refresh();
  }

  function goToProfile() {
    setMenuOpen(false);
    router.push("/dashboard");
  }

  const nav = (key: string) => {
    setOpen(false);
    onNav(key);
  };

  const initials = (user.name || user.email || "U")
    .split(" ")
    .filter(Boolean)
    .map((p) => p[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();

  return (
    <div className="shell-root">
      <aside className={"shell-sidebar" + (open ? " open" : "")}>
        <div className="shell-brand">
            <Link href="/" aria-label="Zoya Ventures Real Estate">
              <img draggable="false" src="/lloo.png" alt="Zoya Ventures Real Estate" />
          </Link>
        </div>
        <nav className="shell-nav">
          {sections.map((s) => (
            <div className="shell-nav-group" key={s.label || "nav"}>
              {s.label && <div className="shell-nav-section">{s.label}</div>}
              {s.items.map((it) =>
                it.href ? (
                  <Link href={it.href} key={it.key} className="shell-nav-item" onClick={() => setOpen(false)}>
                    <span className="shell-nav-icon">
                      <PortalIcon name={it.icon} />
                    </span>
                    <span className="shell-nav-label">{it.label}</span>
                  </Link>
                ) : (
                  <button
                    type="button"
                    key={it.key}
                    className={"shell-nav-item" + (active === it.key ? " active" : "")}
                    onClick={() => nav(it.key)}
                  >
                    <span className="shell-nav-icon">
                      <PortalIcon name={it.icon} />
                    </span>
                    <span className="shell-nav-label">{it.label}</span>
                  </button>
                )
              )}
            </div>
          ))}
        </nav>
        <div className="shell-sidebar-foot">
          <button type="button" className="shell-nav-item logout" onClick={logout} disabled={busy}>
            <span className="shell-nav-icon">
              <PortalIcon name="logout" />
            </span>
            <span className="shell-nav-label">Log out</span>
          </button>
        </div>
      </aside>
      <div className={"shell-backdrop" + (open ? " open" : "")} onClick={() => setOpen(false)} />

      <div className="shell-body">
        <header className="shell-topbar">
          <button type="button" className="shell-burger" aria-label="Open menu" onClick={() => setOpen(true)}>
            <PortalIcon name="menu" />
          </button>
          <div className="shell-topbar-title">{title}</div>
          <div className="shell-user-menu">
            <button type="button" className="shell-user" aria-expanded={menuOpen} onClick={() => setMenuOpen((v) => !v)}>
              <span className="shell-user-avatar">{initials}</span>
              <span className="shell-user-name">{user.name}</span>
              <span className="shell-user-caret">
                <PortalIcon name="expand-more" />
              </span>
            </button>

            {menuOpen && (
              <div className="shell-user-dropdown" role="menu" aria-label="Account menu">
                <button type="button" className="shell-user-option" onClick={goToProfile}>
                  Profile
                </button>
                <button type="button" className="shell-user-option danger" onClick={logout} disabled={busy}>
                  Logout
                </button>
              </div>
            )}
          </div>
        </header>
        <main className="shell-main">
          <div className="shell-container">{children}</div>
        </main>
        <PortalFooter />
      </div>
    </div>
  );
}