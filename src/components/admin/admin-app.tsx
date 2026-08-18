"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { PortalShell, type PortalNavSection, type PortalUser } from "@/components/portal/portal-shell";
import { COUNTRIES, CountryFlag } from "@/components/phone-flag";

type User = PortalUser;

type FieldType = "text" | "textarea" | "number" | "select" | "checkbox" | "json" | "agent";
type FieldDef = { key: string; label: string; type?: FieldType; options?: string[]; full?: boolean; hint?: string; required?: boolean; file?: boolean };

export function AdminApp({ user }: { user: User }) {
  const [tab, setTab] = useState("overview");
  const [pdSlug, setPdSlug] = useState<string | null>(null);

  const sections: PortalNavSection[] = [
    {
      items: [{ key: "back", label: "Back to Website", icon: "launch", href: "/" }],
    },
    {
      label: "Main",
      items: [
        { key: "overview", label: "Dashboard", icon: "home" },
        { key: "properties", label: "Properties", icon: "building" },
        { key: "projects", label: "Projects", icon: "building" },
        { key: "services", label: "Services", icon: "briefcase" },
      ],
    },
    {
      label: "CRM",
      items: [
        { key: "inquiries", label: "Inquiries", icon: "chat" },
        { key: "viewings", label: "Viewings", icon: "calendar" },
        { key: "listings", label: "Listings", icon: "building" },
        { key: "users", label: "Users", icon: "users" },
        { key: "agents", label: "Agents", icon: "person" },
      ],
    },
    {
      label: "Directory",
      items: [
        { key: "developers", label: "Developers", icon: "building" },
        { key: "communities", label: "Areas", icon: "map" },
        { key: "categories", label: "Categories", icon: "tag" },
      ],
    },
    {
      label: "Content",
      items: [
        { key: "testimonials", label: "Testimonials", icon: "star" },
        { key: "faqs", label: "FAQs", icon: "question" },
        { key: "blogs", label: "Blogs", icon: "document" },
        { key: "media", label: "Media", icon: "image" },
        { key: "homepage", label: "Homepage Content", icon: "grid" },
        { key: "more", label: "More", icon: "menu" },
      ],
    },
  ];

  return (
    <PortalShell user={user} title="Admin Panel" sections={sections} active={tab} onNav={setTab}>
      {tab === "overview" && <StatsOverview />}
      {tab === "properties" && <PropertiesManager />}
      {tab === "projects" && (pdSlug ? (
        <ProjectDetailsManager openSlug={pdSlug} onBack={() => setPdSlug(null)} />
      ) : (
        <ResourceManager endpoint="projects" title="New Projects" fields={PROJECT_FIELDS} columns={projectColumns} onDetails={(row) => setPdSlug(row.slug)} />
      ))}
      {tab === "services" && <ResourceManager endpoint="services" title="Services" fields={SERVICE_FIELDS} columns={serviceColumns} />}
      {tab === "users" && <UsersManager />}
      {tab === "inquiries" && <InquiriesManager />}
      {tab === "viewings" && <ViewingsManager />}
      {tab === "listings" && <ListingsManager />}
      {tab === "agents" && <ResourceManager endpoint="agents" title="Agents" fields={AGENT_FIELDS} columns={agentColumns} />}
      {tab === "developers" && <ResourceManager endpoint="developers" title="Developers" fields={DEVELOPER_FIELDS} columns={developerColumns} />}
      {tab === "communities" && <ResourceManager endpoint="communities" title="Communities" fields={COMMUNITY_FIELDS} columns={communityColumns} />}
      {tab === "categories" && <CategoriesManager />}
      {tab === "testimonials" && <ResourceManager endpoint="testimonials" title="Testimonials" fields={TESTIMONIAL_FIELDS} columns={testimonialColumns} />}
      {tab === "faqs" && <ResourceManager endpoint="faqs" title="FAQs" fields={FAQ_FIELDS} columns={faqColumns} />}
      {tab === "media" && <ResourceManager endpoint="media" title="Media Library" fields={MEDIA_FIELDS} columns={mediaColumns} />}
      {tab === "blogs" && <BlogsManager />}
      {tab === "homepage" && <KVManager endpoint="homepage" title="Homepage Content" defaults={HOMEPAGE_KEYS} />}
      {tab === "more" && <MoreManager />}
    </PortalShell>
  );
}

/* ===================== Blogs (read-only list of existing posts) ===================== */

function BlogsManager() {
  const [items, setItems] = useState<any[] | null>(null);
  const [q, setQ] = useState("");
  function load(query: string) {
    fetch(`/api/admin/blogs${query ? `?q=${encodeURIComponent(query)}` : ""}`)
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }
  useEffect(() => {
    load("");
  }, []);
  return (
    <div className="app-card">
      <div className="app-card-head">
        <div>
          <h2>Blog Posts</h2>
          <p className="app-card-sub">{items?.length ?? 0} records</p>
        </div>
        <input className="app-search" placeholder="Search…" value={q} onChange={(e) => setQ(e.target.value)} onKeyDown={(e) => e.key === "Enter" && load(q)} />
      </div>
      {items === null ? (
        <p className="app-empty">Loading…</p>
      ) : items.length === 0 ? (
        <p className="app-empty">No blog posts found.</p>
      ) : (
        <div style={{ overflowX: "auto" }}>
          <table className="app-table">
            <thead><tr><th>Post</th><th>Category</th><th>Date</th><th>Published</th><th></th></tr></thead>
            <tbody>
              {items.map((row) => (
                <tr key={row.slug} className="app-row-click" data-url={`/blog/${row.slug}/`} onClick={(e) => onRowOpen(e, `/blog/${row.slug}/`)}>
                  <td><strong>{row.title}</strong></td>
                  <td>{row.category}</td>
                  <td>{row.date}</td>
                  <td><span className={"app-badge " + (Number(row.published) ? "active" : "inactive")}>{Number(row.published) ? "published" : "draft"}</span></td>
                  <td><div className="row-actions"><a className="app-btn ghost sm" href={`/blog/${row.slug}/`} target="_blank" rel="noopener">View</a></div></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

/* ===================== More (About / Team / Careers / Contact) ===================== */

function MoreManager() {
  const [sub, setSub] = useState("about");
  const tabs = [
    { key: "about", label: "About Us" },
    { key: "team", label: "Meet the Team" },
    { key: "careers", label: "Careers" },
    { key: "contact", label: "Contact Us" },
  ];
  return (
    <div>
      <div className="profile-tabs">
        {tabs.map((t) => (
          <button key={t.key} type="button" className={"profile-tab" + (sub === t.key ? " active" : "")} onClick={() => setSub(t.key)}>
            {t.label}
          </button>
        ))}
      </div>
      {sub === "about" && <AboutManager />}
      {sub === "team" && <ResourceManager endpoint="agents" title="Meet the Team" fields={AGENT_FIELDS} columns={agentColumns} />}
      {sub === "careers" && <ResourceManager endpoint="jobs" title="Careers" fields={JOB_FIELDS} columns={jobColumns} />}
      {sub === "contact" && <KVManager endpoint="contact" title="Contact Us" defaults={CONTACT_KEYS} selects={{ country: true }} />}
    </div>
  );
}

function AboutManager() {
  const [values, setValues] = useState<Record<string, string>>({});
  const [toast, setToast] = useState("");
  const [busy, setBusy] = useState(false);
  useEffect(() => {
    fetch("/api/admin/about")
      .then((r) => r.json())
      .then((d) => {
        const v: Record<string, string> = {};
        for (const k of ABOUT_KEYS) v[k] = "";
        for (const it of d.items || []) v[String(it.key)] = String(it.value || "");
        setValues(v);
      })
      .catch(() => {
        const v: Record<string, string> = {};
        for (const k of ABOUT_KEYS) v[k] = "";
        setValues(v);
      });
  }, []);
  async function save(e: React.FormEvent) {
    e.preventDefault();
    setBusy(true);
    const res = await fetch("/api/admin/about", { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ items: Object.entries(values).map(([key, value]) => ({ key, value })) }) });
    setBusy(false);
    if (res.ok) {
      setToast("Saved");
      setTimeout(() => setToast(""), 2000);
    }
  }
  return (
    <div className="app-card">
      <div className="app-card-head">
        <div>
          <h2>About Us</h2>
          <p className="app-card-sub">Edit the content shown on the public About page. Leave a field empty to keep the current website text.</p>
        </div>
      </div>
      <form className="app-form-grid" onSubmit={save}>
        <div className="app-field full">
          <label>Main paragraph</label>
          <textarea rows={4} value={values.hero_title || ""} onChange={(e) => setValues({ ...values, hero_title: e.target.value })} />
        </div>
        <div className="app-field full">
          <label>Intro text (HTML allowed)</label>
          <textarea rows={8} value={values.intro || ""} onChange={(e) => setValues({ ...values, intro: e.target.value })} />
        </div>
        <div className="full">
          <button type="submit" className="app-btn" disabled={busy}>{busy ? "Saving…" : "Save"}</button>
        </div>
      </form>
      {toast && <div className="app-toast">{toast}</div>}
    </div>
  );
}

/* ===================== Overview ===================== */

function StatsOverview() {
  const [data, setData] = useState<any>(null);
  useEffect(() => {
    fetch("/api/admin/stats")
      .then((r) => r.json())
      .then(setData)
      .catch(() => setData(null));
  }, []);
  if (!data) return <div className="app-card"><p className="app-empty">Loading…</p></div>;
  const s = data.stats;
  return (
    <>
      <div className="app-stats">
        <div className="app-stat"><div className="label">Properties</div><div className="value">{s.properties}</div><div className="sub">{s.publishedProperties} published</div></div>
        <div className="app-stat"><div className="label">Users</div><div className="value">{s.users}</div></div>
        <div className="app-stat"><div className="label">Inquiries</div><div className="value">{s.inquiries}</div><div className="sub">{s.newInquiries} new</div></div>
        <div className="app-stat"><div className="label">Viewings</div><div className="value">{s.viewings}</div><div className="sub">{s.pendingViewings} pending</div></div>
      </div>
      <div className="app-card">
        <div className="app-card-head"><div><h2>Directory</h2></div></div>
        <div className="app-stats">
          <div className="app-stat"><div className="label">Services</div><div className="value">{s.services}</div></div>
          <div className="app-stat"><div className="label">Agents</div><div className="value">{s.agents}</div></div>
          <div className="app-stat"><div className="label">Developers</div><div className="value">{s.developers}</div></div>
          <div className="app-stat"><div className="label">Communities</div><div className="value">{s.communities}</div></div>
          <div className="app-stat"><div className="label">Testimonials</div><div className="value">{s.testimonials}</div></div>
          <div className="app-stat"><div className="label">FAQs</div><div className="value">{s.faqs}</div></div>
          <div className="app-stat"><div className="label">Media items</div><div className="value">{s.media}</div></div>
          <div className="app-stat"><div className="label">Saved properties</div><div className="value">{s.savedProperties}</div></div>
        </div>
      </div>
      {data.recentInquiries.length > 0 && (
        <div className="app-card">
          <div className="app-card-head"><div><h2>Recent inquiries</h2></div></div>
          <table className="app-table">
            <thead><tr><th>Name</th><th>Kind</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              {data.recentInquiries.map((i: any) => (
                <tr key={i.id}>
                  <td><strong>{i.name}</strong><div style={{ fontSize: 12, color: "#9399a4" }}>{i.email}</div></td>
                  <td>{i.kind}</td>
                  <td>{(i.message || "").slice(0, 60)}</td>
                  <td><span className={"app-badge " + i.status}>{i.status}</span></td>
                  <td>{fmtDate(i.created_at)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </>
  );
}

/* ===================== Generic resource manager ===================== */

type Column = { key: string; label: string; render: (row: any) => React.ReactNode };

function ResourceManager({ endpoint, title, fields, columns, onDetails }: { endpoint: string; title: string; fields: FieldDef[]; columns: Column[]; onDetails?: (row: any) => void }) {
  const [items, setItems] = useState<any[] | null>(null);
  const [q, setQ] = useState("");
  const [editing, setEditing] = useState<any | null>(null);
  const [creating, setCreating] = useState(false);
  const [toast, setToast] = useState("");
  const [busy, setBusy] = useState(false);

  const load = useCallback(
    (query = "") => {
      fetch(`/api/admin/${endpoint}?q=${encodeURIComponent(query)}`)
        .then((r) => r.json())
        .then((d) => setItems(d.items || []))
        .catch(() => setItems([]));
    },
    [endpoint]
  );
  useEffect(() => load(), [load]);

  function showToast(msg: string) {
    setToast(msg);
    setTimeout(() => setToast(""), 2200);
  }

  async function save(form: Record<string, any>) {
    setBusy(true);
    const body = coerceJsonFields(form, fields);
    const res = editing
      ? await fetch(`/api/admin/${endpoint}/${editing.id}`, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) })
      : await fetch(`/api/admin/${endpoint}`, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) });
    const d = await res.json().catch(() => ({}));
    if (!res.ok) {
      showToast(d.error || "Save failed");
      setBusy(false);
      return;
    }
    showToast(editing ? "Saved" : "Created");
    setEditing(null);
    setCreating(false);
    setBusy(false);
    load(q);
  }

  async function remove(row: any) {
    if (!confirm(`Delete "${row.title || row.name || row.author || row.question || row.url || row.id}"?`)) return;
    await fetch(`/api/admin/${endpoint}/${row.id}`, { method: "DELETE" });
    showToast("Deleted");
    load(q);
  }

  return (
    <>
      {creating || editing ? (
        <FormPage
          title={editing ? `Edit ${singular(title)}` : `New ${singular(title)}`}
          backLabel={title}
          fields={fields}
          initial={editing || {}}
          busy={busy}
          onCancel={() => { setCreating(false); setEditing(null); }}
          onSave={save}
        />
      ) : (
        <div className="app-card">
          <div className="app-card-head">
            <div>
              <h2>{title}</h2>
              <p className="app-card-sub">{items?.length ?? 0} records</p>
            </div>
            <div style={{ display: "flex", gap: 10 }}>
              <input className="app-search" placeholder="Search…" value={q} onChange={(e) => setQ(e.target.value)} onKeyDown={(e) => e.key === "Enter" && load(q)} />
              <button type="button" className="app-btn" onClick={() => setCreating(true)}>+ Add</button>
            </div>
          </div>
          {items === null ? (
            <p className="app-empty">Loading…</p>
          ) : items.length === 0 ? (
            <p className="app-empty">No records found.</p>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table className="app-table">
                <thead><tr>{columns.map((c) => <th key={c.key}>{c.label}</th>)}<th></th></tr></thead>
                <tbody>
                  {items.map((row) => {
                    const url = rowUrl(endpoint, row);
                    return (
                      <tr key={row.id} className={url ? "app-row-click" : ""} data-url={url || undefined} data-fallback={endpoint === "agents" ? "/team/" : endpoint === "communities" ? "/buy/properties-for-sale/" : undefined} onClick={(e) => onRowOpen(e, url)}>
                        {columns.map((c) => <td key={c.key}>{c.render(row)}</td>)}
                        <td>
                          <div className="row-actions">
                            {onDetails && <button type="button" className="app-btn ghost sm" onClick={() => onDetails(row)}>Details</button>}
                            <button type="button" className="app-btn ghost sm" onClick={() => { setEditing(row); }}>Edit</button>
                            <button type="button" className="app-btn danger sm" onClick={() => remove(row)}>Delete</button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </>
  );
}

function FileField({ value, onValue, textarea }: {
  value: string;
  onValue: (v: string) => void;
  textarea?: boolean;
}) {
  const [busy, setBusy] = useState(false);
  const ref = useRef<HTMLInputElement>(null);
  async function onPick(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    setBusy(true);
    try {
      const fd = new FormData();
      fd.append("file", file);
      const res = await fetch("/api/admin/upload", { method: "POST", body: fd });
      const d = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(d.error || "Upload failed");
      const url = String(d.url);
      if (textarea) {
        const parts = value.split("\n").map((s) => s.trim()).filter(Boolean);
        if (!parts.includes(url)) parts.push(url);
        onValue(parts.join("\n"));
      } else {
        onValue(url);
      }
    } catch (err: any) {
      alert(err.message || "Upload failed");
    } finally {
      setBusy(false);
      if (ref.current) ref.current.value = "";
    }
  }
  return (
    <div style={{ display: "flex", gap: 8, alignItems: "stretch" }}>
      <input type="file" accept="image/*" style={{ display: "none" }} ref={ref} onChange={onPick} />
      {textarea ? (
        <textarea value={value} onChange={(e) => onValue(e.target.value)} style={{ flex: 1 }} />
      ) : (
        <input type="text" value={value} onChange={(e) => onValue(e.target.value)} style={{ flex: 1 }} />
      )}
      <button type="button" className="app-btn ghost sm" disabled={busy} onClick={() => ref.current?.click()}>
        {busy ? "Uploading…" : "From file"}
      </button>
    </div>
  );
}

function FormPage({ title, backLabel, fields, initial, busy, onCancel, onSave }: {
  title: string;
  backLabel: string;
  fields: FieldDef[];
  initial: Record<string, any>;
  busy: boolean;
  onCancel: () => void;
  onSave: (form: Record<string, any>) => void;
}) {
  const [form, setForm] = useState<Record<string, any>>(() => {
    const f: Record<string, any> = {};
    for (const fd of fields) {
      f[fd.key] = fd.type === "checkbox" ? Boolean(Number(initial[fd.key])) : initial[fd.key] ?? "";
    }
    return f;
  });
  function set(key: string, value: any) {
    setForm((f) => ({ ...f, [key]: value }));
  }
  return (
    <div className="app-card">
      <div className="app-card-head">
        <div>
          <h2>{title}</h2>
          <button type="button" className="app-btn ghost sm form-page-back" onClick={onCancel}>← Back to {backLabel}</button>
        </div>
      </div>
      <form
        className="app-form-grid"
        onSubmit={(e) => {
          e.preventDefault();
          onSave(form);
        }}
      >
        {fields.map((fd) => (
          <div className={"app-field" + (fd.full ? " full" : "")} key={fd.key}>
            <label>{fd.label}</label>
            {fd.type === "textarea" ? (
              fd.file ? (
                <FileField textarea value={form[fd.key] || ""} onValue={(v) => set(fd.key, v)} />
              ) : (
                <textarea value={form[fd.key] || ""} onChange={(e) => set(fd.key, e.target.value)} />
              )
            ) : fd.type === "number" ? (
              <input type="number" value={form[fd.key] ?? ""} onChange={(e) => set(fd.key, e.target.value)} />
            ) : fd.type === "select" ? (
              <select value={form[fd.key] || ""} onChange={(e) => set(fd.key, e.target.value)}>
                <option value="">—</option>
                {(fd.options || []).map((o) => <option key={o} value={o}>{o}</option>)}
              </select>
            ) : fd.type === "checkbox" ? (
              <div className="app-check-row">
                <input type="checkbox" checked={Boolean(form[fd.key])} onChange={(e) => set(fd.key, e.target.checked ? 1 : 0)} />
                <span style={{ fontSize: 13 }}>{fd.hint || "Enabled"}</span>
              </div>
            ) : fd.type === "json" ? (
              fd.file ? (
                <FileField value={Array.isArray(form[fd.key]) ? form[fd.key].join(", ") : (form[fd.key] || "")} onValue={(v) => set(fd.key, v)} />
              ) : (
                <input
                  type="text"
                  placeholder="Comma separated values"
                  value={Array.isArray(form[fd.key]) ? form[fd.key].join(", ") : (form[fd.key] || "")}
                  onChange={(e) => set(fd.key, e.target.value)}
                />
              )
            ) : (
              fd.file ? (
                <FileField value={form[fd.key] || ""} onValue={(v) => set(fd.key, v)} />
              ) : (
                <input type="text" value={form[fd.key] || ""} onChange={(e) => set(fd.key, e.target.value)} />
              )
            )}
            {fd.hint && fd.type !== "checkbox" && <div className="hint">{fd.hint}</div>}
          </div>
        ))}
        <div className="form-actions full">
          <button type="button" className="app-btn ghost" onClick={onCancel}>Cancel</button>
          <button type="submit" className="app-btn" disabled={busy}>{busy ? "Saving…" : "Save"}</button>
        </div>
      </form>
    </div>
  );
}

/* ===================== Properties ===================== */

const PROPERTY_FIELDS: FieldDef[] = [
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
  { key: "agent_id", label: "Assigned agent", type: "agent", full: true, hint: "Shown as the negotiator on the public property page" },
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

const SERVICE_FIELDS: FieldDef[] = [
  { key: "title", label: "Title", required: true, full: true },
  { key: "slug", label: "Slug", required: true, full: true },
  { key: "icon", label: "Icon URL", full: true, file: true },
  { key: "banner_image", label: "Banner image URL", full: true, file: true },
  { key: "description", label: "Description", type: "textarea", full: true },
  { key: "rich_content", label: "Rich content (HTML)", type: "textarea", full: true },
  { key: "gallery", label: "Gallery URLs", type: "json", full: true, file: true },
  { key: "seo_title", label: "SEO title", full: true },
  { key: "seo_description", label: "SEO description", full: true },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the public site" },
];

const AGENT_FIELDS: FieldDef[] = [
  { key: "name", label: "Name", required: true, full: true },
  { key: "slug", label: "Slug", required: true, full: true },
  { key: "role", label: "Role" },
  { key: "phone", label: "Phone" },
  { key: "email", label: "Email", full: true },
  { key: "brn_number", label: "BRN number" },
  { key: "img", label: "Profile image URL", full: true, file: true },
  { key: "languages", label: "Languages", type: "json", full: true },
  { key: "specialties", label: "Specialties", type: "json", full: true },
  { key: "bio", label: "Bio", type: "textarea", full: true },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the team page" },
];

const DEVELOPER_FIELDS: FieldDef[] = [
  { key: "name", label: "Name", required: true, full: true },
  { key: "slug", label: "Slug", required: true, full: true },
  { key: "region", label: "Region" },
  { key: "founded", label: "Founded", type: "number" },
  { key: "deliveries", label: "Deliveries", type: "number" },
  { key: "img", label: "Logo URL", full: true, file: true },
  { key: "description", label: "Description", type: "textarea", full: true },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the public site" },
];

const COMMUNITY_FIELDS: FieldDef[] = [
  { key: "name", label: "Name", required: true, full: true },
  { key: "slug", label: "Slug", required: true, full: true },
  { key: "region", label: "Region", full: true },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the public site" },
];

const TESTIMONIAL_FIELDS: FieldDef[] = [
  { key: "author", label: "Author", required: true, full: true },
  { key: "role", label: "Role", full: true },
  { key: "content", label: "Content", type: "textarea", full: true },
  { key: "rating", label: "Rating (1–5)", type: "number" },
  { key: "img", label: "Photo URL", full: true, file: true },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the public site" },
];

const FAQ_FIELDS: FieldDef[] = [
  { key: "question", label: "Question", required: true, full: true },
  { key: "answer", label: "Answer", type: "textarea", full: true },
  { key: "category", label: "Category" },
  { key: "sort", label: "Sort order", type: "number" },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the public site" },
];

const MEDIA_FIELDS: FieldDef[] = [
  { key: "url", label: "URL", required: true, full: true, file: true },
  { key: "kind", label: "Kind", type: "select", options: ["image", "video", "floorplan", "brochure"] },
  { key: "alt", label: "Alt text", full: true },
];

const JOB_FIELDS: FieldDef[] = [
  { key: "title", label: "Job title", required: true, full: true },
  { key: "slug", label: "Slug", required: true, full: true },
  { key: "location", label: "Location" },
  { key: "summary", label: "Summary", type: "textarea", full: true },
  { key: "job_details", label: "Full details (HTML)", type: "textarea", full: true },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the careers page" },
];

const PROJECT_FIELDS: FieldDef[] = [
  { key: "title", label: "Project name", required: true, full: true },
  { key: "slug", label: "Slug", required: true, full: true },
  { key: "status", label: "Status", type: "select", options: ["ready", "pending", "under_construction", "future_launch", "off-plan"] },
  { key: "price", label: "Price (AED)", type: "number" },
  { key: "currency", label: "Currency" },
  { key: "bedrooms_min", label: "Bedrooms (min)", type: "number" },
  { key: "bedrooms_max", label: "Bedrooms (max)", type: "number" },
  { key: "completion_year", label: "Completion year", type: "number" },
  { key: "community", label: "Community", full: true },
  { key: "developer", label: "Developer", full: true },
  { key: "department", label: "Department" },
  { key: "display_address", label: "Display address", full: true },
  { key: "building_type", label: "Building types (comma-separated)", type: "json", full: true },
  { key: "about", label: "About (HTML)", type: "textarea", full: true },
  { key: "images", label: "Image URLs (comma-separated)", type: "json", full: true, file: true },
  { key: "amenities", label: "Amenities (comma-separated)", type: "json", full: true },
  { key: "banner_image", label: "Banner image URL", full: true, file: true },
  { key: "published", label: "Published", type: "checkbox", hint: "Visible on the public site" },
];

const ABOUT_KEYS = ["hero_title", "intro"];

const CONTACT_KEYS = ["country", "phone", "email", "whatsapp", "address", "office_hours"];
const HOMEPAGE_KEYS = ["hero_title", "hero_subtitle", "announcement_bar", "stats_heading", "featured_heading"];

function rowUrl(endpoint: string, row: any): string {
  switch (endpoint) {
    case "properties":
      return `/${row.transaction_type || "buy"}/${row.slug}${row.id}/`;
    case "projects":
      return row.slug ? `/new-projects/${row.slug}/` : "";
    case "services":
      return row.slug ? `/property-services/${row.slug}/` : "";
    case "agents":
      return row.slug ? `/team/${row.slug}/` : "";
    case "developers":
      return row.slug ? `/new-projects/developed-by-${row.slug}/` : "";
    case "communities":
      return row.slug ? `/buy/properties-for-sale/in-${row.slug}/` : "";
  }
  return "";
}

function onRowOpen(e: React.MouseEvent<HTMLElement>, url: string) {
  if (!url) return;
  const target = e.target as HTMLElement;
  if (target.closest("button, a, input, select, textarea")) return;
  const tr = target.closest("tr");
  const fb = tr?.getAttribute("data-fallback");
  if (!fb) {
    window.open(url, "_blank");
    return;
  }
  fetch(url, { method: "GET", credentials: "same-origin" })
    .then((r) => window.open(r.ok ? url : fb, "_blank"))
    .catch(() => window.open(fb, "_blank"));
}

function coerceJsonFields(form: Record<string, any>, fields: FieldDef[]): Record<string, any> {
  const out: Record<string, any> = {};
  for (const fd of fields) {
    let v = form[fd.key];
    if (fd.type === "json" && typeof v === "string") {
      v = v.split(",").map((s) => s.trim()).filter(Boolean);
    }
    if (fd.type === "number" && v === "") v = 0;
    out[fd.key] = v;
  }
  return out;
}

/* ===== Properties manager (custom) ===== */

function PropertiesManager() {
  const [items, setItems] = useState<any[] | null>(null);
  const [q, setQ] = useState("");
  const [editing, setEditing] = useState<any | null>(null);
  const [creating, setCreating] = useState(false);
  const [toast, setToast] = useState("");
  const [busy, setBusy] = useState(false);

  const load = useCallback((query = "") => {
    fetch(`/api/admin/properties?q=${encodeURIComponent(query)}`)
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }, []);
  useEffect(() => load(), [load]);

  function showToast(msg: string) {
    setToast(msg);
    setTimeout(() => setToast(""), 2200);
  }

  async function remove(row: any) {
    if (!confirm(`Delete "${row.title}"?`)) return;
    await fetch(`/api/admin/properties?id=${row.id}`, { method: "DELETE" });
    showToast("Deleted");
    load(q);
  }

  function openEdit(row: any) {
    fetch(`/api/admin/properties/${row.id}`)
      .then((r) => r.json())
      .then((d) => setEditing(d.item || null))
      .catch(() => showToast("Could not load property"));
  }

  return (
    <>
      {creating || editing ? (
        <PropertyForm
          key={editing?.id ?? "new"}
          initial={editing}
          busy={busy}
          onCancel={() => { setCreating(false); setEditing(null); }}
          onDone={(msg) => { showToast(msg); setCreating(false); setEditing(null); load(q); }}
          setBusy={setBusy}
        />
      ) : (
        <div className="app-card">
          <div className="app-card-head">
            <div>
              <h2>Properties</h2>
              <p className="app-card-sub">{items?.length ?? 0} records — created properties appear on the public site</p>
            </div>
            <div style={{ display: "flex", gap: 10 }}>
              <input className="app-search" placeholder="Search title, slug, developer…" value={q} onChange={(e) => setQ(e.target.value)} onKeyDown={(e) => e.key === "Enter" && load(q)} />
              <button type="button" className="app-btn" onClick={() => setCreating(true)}>+ New property</button>
            </div>
          </div>
          {items === null ? (
            <p className="app-empty">Loading…</p>
          ) : items.length === 0 ? (
            <p className="app-empty">No properties yet. Create your first one.</p>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table className="app-table">
                <thead>
                  <tr><th>Title</th><th>Type</th><th>Price</th><th>Beds</th><th>Media</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                  {items.map((row) => (
                    <tr key={row.id} className="app-row-click" onClick={(e) => onRowOpen(e, rowUrl("properties", row))}>
                      <td>
                        <strong>{row.title}</strong>
                        <div style={{ fontSize: 12, color: "#9399a4" }}>/{row.transaction_type}/{row.slug}{row.id}/</div>
                      </td>
                      <td>{row.property_type}</td>
                      <td>{"AED " + Number(row.price).toLocaleString()}</td>
                      <td>{row.bedroom} bd / {row.bathroom} ba</td>
                      <td>{row.image_count} img · {row.amenity_count} am.</td>
                      <td>
                        <span className={"app-badge " + (Number(row.published) ? "active" : "inactive")}>
                          {Number(row.published) ? "published" : "draft"}
                        </span>
                        {Number(row.featured) === 1 && <span className="app-badge" style={{ background: "#fff3e0", color: "#b26a00", marginLeft: 4 }}>featured</span>}
                      </td>
                      <td>
                        <div className="row-actions">
                          <button type="button" className="app-btn ghost sm" onClick={() => openEdit(row)}>Edit</button>
                          <button type="button" className="app-btn danger sm" onClick={() => remove(row)}>Delete</button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </>
  );
}

function PropertyForm({ initial, busy, setBusy, onCancel, onDone }: {
  initial: any | null;
  busy: boolean;
  setBusy: (b: boolean) => void;
  onCancel: () => void;
  onDone: (msg: string) => void;
}) {
  const [form, setForm] = useState<Record<string, any>>(() => {
    const f: Record<string, any> = {};
    for (const fd of PROPERTY_FIELDS) {
      f[fd.key] = fd.type === "checkbox" ? Boolean(Number(initial?.[fd.key])) : (initial?.[fd.key] ?? "");
    }
    return f;
  });
  const [media, setMedia] = useState<{ kind: string; url: string }[]>(
    (initial?.media || []).map((m: any) => ({ kind: m.kind || "image", url: m.url || "" }))
  );
  const [amenityList, setAmenityList] = useState<string[]>([]);
  const [selectedAmenities, setSelectedAmenities] = useState<string[]>(initial?.amenities || []);
  const [newAmenity, setNewAmenity] = useState("");
  const [uploading, setUploading] = useState<{ done: number; total: number } | null>(null);
  const imageRef = useRef<HTMLInputElement>(null);
  const videoRef = useRef<HTMLInputElement>(null);

  const [agents, setAgents] = useState<any[]>([]);
  const [agentQuery, setAgentQuery] = useState("");
  const [agentOpen, setAgentOpen] = useState(false);
  const agentRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    fetch("/api/admin/agents")
      .then((r) => r.json())
      .then((d) => setAgents((d.items || []).filter((a: any) => Number(a.published) === 1)))
      .catch(() => setAgents([]));
  }, []);

  useEffect(() => {
    function onDocClick(e: MouseEvent) {
      if (agentRef.current && !agentRef.current.contains(e.target as Node)) setAgentOpen(false);
    }
    document.addEventListener("mousedown", onDocClick);
    return () => document.removeEventListener("mousedown", onDocClick);
  }, []);

  const selectedAgent = agents.find((a) => Number(a.id) === Number(form.agent_id));
  const filteredAgents = agents.filter((a) => {
    const q = agentQuery.trim().toLowerCase();
    if (!q) return true;
    return String(a.name || "").toLowerCase().includes(q) || String(a.role || "").toLowerCase().includes(q);
  });

  useEffect(() => {
    fetch("/api/admin/amenities")
      .then((r) => r.json())
      .then((d) => setAmenityList((d.items || []).map((a: any) => String(a.name))))
      .catch(() => setAmenityList([]));
  }, []);

  function set(key: string, value: any) {
    setForm((f) => ({ ...f, [key]: value }));
  }
  function toggleAmenity(name: string) {
    setSelectedAmenities((s) => (s.includes(name) ? s.filter((x) => x !== name) : [...s, name]));
  }
  async function deleteAmenity(name: string) {
    await fetch(`/api/admin/amenities?name=${encodeURIComponent(name)}`, { method: "DELETE" }).catch(() => null);
    setAmenityList((l) => l.filter((x) => x !== name));
    setSelectedAmenities((s) => s.filter((x) => x !== name));
  }
  async function addAmenity() {
    const name = newAmenity.trim();
    if (!name || amenityList.includes(name)) return;
    await fetch("/api/admin/amenities", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ name }) });
    setAmenityList((l) => [...l, name]);
    setSelectedAmenities((s) => [...s, name]);
    setNewAmenity("");
  }

  async function uploadFiles(e: React.ChangeEvent<HTMLInputElement>, kind: string) {
    const files = Array.from(e.target.files || []);
    e.target.value = "";
    if (!files.length) return;
    setUploading({ done: 0, total: files.length });
    for (const file of files) {
      try {
        const fd = new FormData();
        fd.append("file", file);
        const res = await fetch("/api/admin/upload", { method: "POST", body: fd });
        const d = await res.json().catch(() => ({}));
        if (res.ok && d.url) {
          setMedia((ms) => [...ms, { kind, url: String(d.url) }]);
        } else {
          alert(d.error || "Upload failed");
        }
      } catch {
        alert("Upload failed");
      }
      setUploading((u) => (u ? { done: u.done + 1, total: u.total } : u));
    }
    setUploading(null);
  }

  function mediaFileName(url: string): string {
    try {
      const clean = url.split("?")[0].split("#")[0];
      const name = decodeURIComponent(clean.split("/").pop() || "");
      return name || url;
    } catch {
      return url;
    }
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setBusy(true);
    const body = {
      ...coerceJsonFields(form, PROPERTY_FIELDS),
      amenities: selectedAmenities,
      media: media.filter((m) => m.url.trim()),
    };
    try {
      const res = initial
        ? await fetch(`/api/admin/properties?id=${initial.id}`, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) })
        : await fetch("/api/admin/properties", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) });
      const d = await res.json().catch(() => ({}));
      if (!res.ok) {
        alert(d.error || "Save failed");
        return;
      }
      onDone(initial ? "Property saved" : "Property created");
    } catch {
      alert("Save failed");
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="app-card">
      <div className="app-card-head">
        <div>
          <h2>{initial ? "Edit property" : "New property"}</h2>
          <p className="app-card-sub">{initial ? "Update the property details below." : "Fill in the details to publish a new property."}</p>
        </div>
        <button type="button" className="app-btn ghost sm" onClick={onCancel}>← Back to Properties</button>
      </div>
      <form className="app-form-grid" onSubmit={submit}>
          <div className="full" style={{ borderBottom: "1px solid #f0f3f8", paddingBottom: 12 }}>
            <strong style={{ color: "#142121", fontSize: 14 }}>General information</strong>
          </div>
          {PROPERTY_FIELDS.map((fd) => (
            <div className={"app-field" + (fd.full ? " full" : "")} key={fd.key}>
              <label>{fd.label}</label>
              {fd.type === "textarea" ? (
                <textarea value={form[fd.key] || ""} onChange={(e) => set(fd.key, e.target.value)} />
              ) : fd.type === "number" ? (
                <input type="number" value={form[fd.key] ?? ""} onChange={(e) => set(fd.key, e.target.value)} />
              ) : fd.type === "select" ? (
                <select value={form[fd.key] || ""} onChange={(e) => set(fd.key, e.target.value)}>
                  <option value="">—</option>
                  {(fd.options || []).map((o) => <option key={o} value={o}>{o}</option>)}
                </select>
              ) : fd.type === "checkbox" ? (
                <div className="app-check-row">
                  <input type="checkbox" checked={Boolean(form[fd.key])} onChange={(e) => set(fd.key, e.target.checked ? 1 : 0)} />
                  <span style={{ fontSize: 13 }}>{fd.hint || "Enabled"}</span>
                </div>
              ) : fd.type === "agent" ? (
                <div className="agent-select" ref={agentRef}>
                  <div className="agent-select-current" onClick={() => setAgentOpen((o) => !o)}>
                    <span className={selectedAgent ? "" : "agent-select-placeholder"}>
                      {selectedAgent ? selectedAgent.name : "Select an agent…"}
                    </span>
                    <span className="agent-select-arrow">▾</span>
                  </div>
                  {agentOpen && (
                    <div className="agent-select-menu">
                      <input
                        className="agent-select-search"
                        placeholder="Search agents…"
                        value={agentQuery}
                        onChange={(e) => setAgentQuery(e.target.value)}
                      />
                      <div className="agent-select-list">
                        {filteredAgents.map((a) => (
                          <button
                            type="button"
                            key={a.id}
                            className={"agent-select-option" + (Number(a.id) === Number(form.agent_id) ? " selected" : "")}
                            onClick={() => {
                              set("agent_id", a.id);
                              setAgentOpen(false);
                              setAgentQuery("");
                            }}
                          >
                            <span className="agent-select-avatar">
                              {a.img ? <img src={a.img} alt="" /> : a.name?.charAt(0)?.toUpperCase() || "?"}
                            </span>
                            <span className="agent-select-meta">
                              <strong>{a.name}</strong>
                              {a.role ? <small>{a.role}</small> : null}
                            </span>
                          </button>
                        ))}
                        {!filteredAgents.length && <div className="agent-select-empty">No agents found</div>}
                      </div>
                    </div>
                  )}
                  {form.agent_id !== "" && (
                    <button
                      type="button"
                      className="agent-select-clear"
                      onClick={() => {
                        set("agent_id", "");
                        setAgentQuery("");
                      }}
                    >
                      Clear
                    </button>
                  )}
                </div>
              ) : (
                <input type="text" value={form[fd.key] || ""} onChange={(e) => set(fd.key, e.target.value)} />
              )}
              {fd.hint && fd.type !== "checkbox" && <div className="hint">{fd.hint}</div>}
            </div>
          ))}

          <div className="full" style={{ borderBottom: "1px solid #f0f3f8", paddingBottom: 12 }}>
            <strong style={{ color: "#142121", fontSize: 14 }}>Media</strong>
          </div>
          {media.map((m, i) => (
            <div className="full" style={{ display: "flex", gap: 10, alignItems: "center" }} key={i}>
              {m.kind === "image" && m.url ? (
                <img
                  src={m.url}
                  alt=""
                  style={{ border: "1px solid #e1e8ed", borderRadius: 4, height: 40, objectFit: "cover", width: 56 }}
                />
              ) : (
                <span style={{ color: "#9399a4", flex: "0 0 56px", fontSize: 10, letterSpacing: "0.5px", textAlign: "center" }}>VIDEO</span>
              )}
              <select
                style={{ flex: "0 0 130px", border: "1px solid #e1e8ed", borderRadius: 6, padding: "8px" }}
                value={m.kind}
                onChange={(e) => setMedia(media.map((x, j) => (j === i ? { ...x, kind: e.target.value } : x)))}
              >
                {["image", "video", "floorplan", "brochure"].map((k) => <option key={k} value={k}>{k}</option>)}
              </select>
              <span style={{ color: "#35373c", flex: 1, fontSize: 13, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }} title={m.url}>
                {mediaFileName(m.url)}
              </span>
              <button type="button" className="app-btn danger sm" onClick={() => setMedia(media.filter((_, j) => j !== i))}>×</button>
            </div>
          ))}
          <div className="full" style={{ display: "flex", gap: 8, alignItems: "center" }}>
            <input
              ref={imageRef}
              type="file"
              accept="image/*"
              multiple
              style={{ display: "none" }}
              onChange={(e) => uploadFiles(e, "image")}
            />
            <input
              ref={videoRef}
              type="file"
              accept="video/*"
              multiple
              style={{ display: "none" }}
              onChange={(e) => uploadFiles(e, "video")}
            />
            <button type="button" className="app-btn sm" disabled={!!uploading} onClick={() => imageRef.current?.click()}>
              + Add images
            </button>
            <button type="button" className="app-btn ghost sm" disabled={!!uploading} onClick={() => videoRef.current?.click()}>
              + Add video
            </button>
            {uploading && (
              <span className="hint">Uploading {uploading.done + 1}/{uploading.total}…</span>
            )}
          </div>

          <div className="full" style={{ borderBottom: "1px solid #f0f3f8", paddingBottom: 12 }}>
            <strong style={{ color: "#142121", fontSize: 14 }}>Amenities</strong>
          </div>
          <div className="full">
            <div style={{ display: "flex", gap: 8 }}>
              <input
                className="app-search"
                style={{ flex: 1 }}
                placeholder="Add a new amenity (e.g. Smart Home)"
                value={newAmenity}
                onChange={(e) => setNewAmenity(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && (e.preventDefault(), addAmenity())}
              />
              <button type="button" className="app-btn ghost sm" onClick={addAmenity}>Add</button>
            </div>
            <div className="app-chip-list">
              {amenityList.map((a) => (
                <div key={a} className={"app-chip" + (selectedAmenities.includes(a) ? " active" : "")} onClick={() => toggleAmenity(a)}>
                  <span className="app-chip-label">{a}</span>
                  <span
                    className="app-chip-x"
                    title={`Delete ${a}`}
                    onClick={(e) => {
                      e.stopPropagation();
                      deleteAmenity(a);
                    }}
                  >
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M1 1L9 9M9 1L1 9" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                    </svg>
                  </span>
                </div>
              ))}
            </div>
            {selectedAmenities.length > 0 && (
              <div className="hint">{selectedAmenities.length} amenity(ies) selected</div>
            )}
          </div>

          <div className="form-actions full">
            <button type="button" className="app-btn ghost" onClick={onCancel}>Cancel</button>
            <button type="submit" className="app-btn" disabled={busy}>{busy ? "Saving…" : "Save property"}</button>
          </div>
        </form>
    </div>
  );
}

/* ===================== Users ===================== */

function UsersManager() {
  const [items, setItems] = useState<any[] | null>(null);
  const [editing, setEditing] = useState<any | null>(null);
  const [creating, setCreating] = useState(false);
  const [toast, setToast] = useState("");
  const [busy, setBusy] = useState(false);

  const load = useCallback(() => {
    fetch("/api/admin/users")
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }, []);
  useEffect(load, [load]);

  function showToast(msg: string) {
    setToast(msg);
    setTimeout(() => setToast(""), 2200);
  }

  async function save(form: any, id?: number) {
    setBusy(true);
    const body: Record<string, any> = {
      name: form.name,
      email: form.email,
      phone: form.phone,
      role: form.role,
      is_active: form.is_active ? 1 : 0,
    };
    if (form.password) body.password = form.password;
    const res = id
      ? await fetch(`/api/admin/users/${id}`, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) })
      : await fetch("/api/admin/users", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) });
    const d = await res.json().catch(() => ({}));
    setBusy(false);
    if (!res.ok) {
      showToast(d.error || "Save failed");
      return;
    }
    showToast(id ? "User updated" : "User created");
    setCreating(false);
    setEditing(null);
    load();
  }

  async function toggleActive(row: any) {
    await fetch(`/api/admin/users/${row.id}`, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ is_active: Number(row.is_active) ? 0 : 1 }) });
    load();
  }

  return (
    <>
      {creating || editing ? (
        <UserForm
          user={editing}
          busy={busy}
          onCancel={() => { setCreating(false); setEditing(null); }}
          onSave={(f) => save(f, editing?.id)}
        />
      ) : (
        <div className="app-card">
          <div className="app-card-head">
            <div>
              <h2>Users</h2>
              <p className="app-card-sub">{items?.length ?? 0} accounts</p>
            </div>
            <button type="button" className="app-btn" onClick={() => setCreating(true)}>+ Add user</button>
          </div>
          {items === null ? (
            <p className="app-empty">Loading…</p>
          ) : items.length === 0 ? (
            <p className="app-empty">No users.</p>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table className="app-table">
                <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last login</th><th></th></tr></thead>
                <tbody>
                  {items.map((row) => (
                    <tr key={row.id}>
                      <td><strong>{row.name}</strong><div style={{ fontSize: 12, color: "#9399a4" }}>{row.email}{row.phone ? " · " + row.phone : ""}{row.phone ? " " : ""}{row.phone ? <CountryFlag /> : null}</div></td>
                      <td><span className="app-badge" style={{ background: row.role === "admin" ? "#e3f2fd" : "#f0f3f8", color: "#075985" }}>{row.role}</span></td>
                      <td><span className={"app-badge " + (Number(row.is_active) ? "active" : "inactive")}>{Number(row.is_active) ? "active" : "disabled"}</span></td>
                      <td>{row.last_login_at ? fmtDate(row.last_login_at) : "—"}</td>
                      <td>
                        <div className="row-actions">
                          <button type="button" className="app-btn ghost sm" onClick={() => setEditing(row)}>Edit</button>
                          <button type="button" className="app-btn ghost sm" onClick={() => toggleActive(row)}>{Number(row.is_active) ? "Disable" : "Enable"}</button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </>
  );
}

function UserForm({ user, busy, onCancel, onSave }: { user: any | null; busy: boolean; onCancel: () => void; onSave: (f: any) => void }) {
  const [form, setForm] = useState({
    name: user?.name || "",
    email: user?.email || "",
    phone: user?.phone || "",
    role: user?.role || "user",
    is_active: user ? Boolean(Number(user.is_active)) : true,
    password: "",
  });
  return (
    <div className="app-card">
      <div className="app-card-head">
        <div>
          <h2>{user ? "Edit user" : "Add user"}</h2>
        </div>
        <button type="button" className="app-btn ghost sm" onClick={onCancel}>← Back to Users</button>
      </div>
      <form className="app-form-grid" onSubmit={(e) => { e.preventDefault(); onSave(form); }}>
        <div className="app-field"><label>Full name</label><input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required /></div>
        <div className="app-field"><label>Email</label><input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required /></div>
        <div className="app-field"><label>Phone</label><input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} /></div>
        <div className="app-field">
          <label>Role</label>
          <select value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })}>
            <option value="user">user</option>
            <option value="agent">agent</option>
            <option value="admin">admin</option>
          </select>
        </div>
        <div className="app-field full">
          <label>{user ? "New password (leave blank to keep)" : "Password"}</label>
          <input type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} placeholder={user ? "" : "Min 8 chars, letters + numbers"} required={!user} />
        </div>
        <div className="app-field full">
          <div className="app-check-row">
            <input type="checkbox" checked={form.is_active} onChange={(e) => setForm({ ...form, is_active: e.target.checked })} />
            <span style={{ fontSize: 13 }}>Account active</span>
          </div>
        </div>
        <div className="form-actions full">
          <button type="button" className="app-btn ghost" onClick={onCancel}>Cancel</button>
          <button type="submit" className="app-btn" disabled={busy}>{busy ? "Saving…" : "Save"}</button>
        </div>
      </form>
    </div>
  );
}

/* ===================== Inquiries & Viewings ===================== */

function DetailWindow({ title, onClose, children }: { title: string; onClose: () => void; children: React.ReactNode }) {
  return (
    <div className="app-modal-backdrop" onClick={onClose}>
      <div className="app-modal" role="dialog" aria-modal="true" onClick={(e) => e.stopPropagation()}>
        <div className="app-modal-head">
          <h3>{title}</h3>
          <button type="button" className="app-modal-close" aria-label="Close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
              <path d="M18 6 6 18M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div className="app-modal-body">{children}</div>
      </div>
    </div>
  );
}

function DetailGroup({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div className="app-detail-group">
      <p className="app-detail-group-label">{label}</p>
      <div className="app-detail-group-body">{children}</div>
    </div>
  );
}

function DetailField({ label, value }: { label: string; value?: string | null }) {
  return (
    <div className="app-detail-field">
      <p className="app-detail-label">{label}</p>
      <p className="app-detail-value">{value || "—"}</p>
    </div>
  );
}

function parseListingPayload(raw: string): Record<string, string> | null {
  if (!raw) return null;
  try {
    const j = JSON.parse(raw);
    if (j && typeof j === "object" && !Array.isArray(j)) {
      const out: Record<string, string> = {};
      for (const [k, v] of Object.entries(j)) if (v !== "" && v != null) out[k] = String(v);
      return Object.keys(out).length ? out : null;
    }
  } catch {
    /* not JSON */
  }
  return null;
}

function InquiriesManager() {
  const [items, setItems] = useState<any[] | null>(null);
  const [open, setOpen] = useState<any | null>(null);
  const [toast, setToast] = useState("");
  const load = useCallback(() => {
    fetch("/api/admin/inquiries")
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }, []);
  useEffect(load, [load]);
  async function setStatus(row: any, status: string) {
    await fetch("/api/admin/inquiries", { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id, status }) });
    setToast(`Inquiry marked "${status}"`);
    setTimeout(() => setToast(""), 2000);
    load();
  }
  async function remove(row: any) {
    if (!confirm("Delete this inquiry?")) return;
    await fetch("/api/admin/inquiries", { method: "DELETE", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id }) });
    setOpen(null);
    load();
  }
  const payload = open ? parseListingPayload(String(open.message || "")) : null;
  return (
    <div className="app-card">
      <div className="app-card-head"><div><h2>Inquiries</h2><p className="app-card-sub">{items?.length ?? 0} messages</p></div></div>
      {items === null ? (
        <p className="app-empty">Loading…</p>
      ) : items.length === 0 ? (
        <p className="app-empty">No inquiries yet.</p>
      ) : (
        <div style={{ overflowX: "auto" }}>
          <table className="app-table">
            <thead><tr><th>Contact</th><th>Kind</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              {items.map((row) => (
                <tr key={row.id} className="app-row-click" onClick={() => setOpen(row)}>
                  <td><strong>{row.name}</strong><div style={{ fontSize: 12, color: "#9399a4" }}>{row.email}{row.phone ? " · " + row.phone : ""}</div></td>
                  <td>{row.kind}</td>
                  <td><span className="app-badge">{row.status}</span></td>
                  <td>{fmtDate(row.created_at)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      {open && (
        <DetailWindow title="Inquiry Details" onClose={() => setOpen(null)}>
          {payload ? (
            <>
              <DetailGroup label="Contact">
                <DetailField label="Name" value={open.name} />
                <DetailField label="Email" value={open.email} />
                <DetailField label="Phone" value={open.phone} />
              </DetailGroup>
              <DetailGroup label="Listing Details">
                <DetailField label="Transaction" value={payload.transaction} />
                <DetailField label="Property Type" value={payload.property_type} />
                <DetailField label="Community / Area" value={payload.community} />
                <DetailField label="Bedrooms" value={payload.bedrooms} />
                <DetailField label="Bathrooms" value={payload.bathrooms} />
                <DetailField label="Size (sq ft)" value={payload.size_sqft} />
                <DetailField label="Expected Price (AED)" value={payload.expected_price} />
                <DetailField label="Ownership Status" value={payload.ownership} />
                <DetailField label="Message" value={payload.message} />
              </DetailGroup>
            </>
          ) : (
            <>
              <DetailGroup label="Contact">
                <DetailField label="Name" value={open.name} />
                <DetailField label="Email" value={open.email} />
                <DetailField label="Phone" value={open.phone} />
              </DetailGroup>
              <DetailGroup label="Enquiry">
                <DetailField label="Kind" value={open.kind} />
                <DetailField label="Property" value={open.property_slug || open.property_ref} />
                <DetailField label="Message" value={open.message} />
              </DetailGroup>
            </>
          )}
          <DetailGroup label="Details">
            <DetailField label="Received" value={open.created_at ? new Date(open.created_at).toLocaleString("en-GB") : ""} />
            <DetailField label="User" value={open.user_name || open.user_email} />
          </DetailGroup>
          <div className="app-detail-actions">
            <select
              style={{ border: "1px solid #e1e8ed", borderRadius: 6, fontSize: 12, padding: "6px 10px" }}
              value={open.status}
              onChange={(e) => setStatus(open, e.target.value)}
            >
              {["new", "contacted", "closed"].map((s) => <option key={s} value={s}>{s}</option>)}
            </select>
            <button type="button" className="app-btn danger sm" onClick={() => remove(open)}>Delete</button>
          </div>
        </DetailWindow>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </div>
  );
}

function ListingsManager() {
  const [items, setItems] = useState<any[] | null>(null);
  const [open, setOpen] = useState<any | null>(null);
  const [toast, setToast] = useState("");
  const load = useCallback(() => {
    fetch("/api/admin/inquiries?kind=listing")
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }, []);
  useEffect(load, [load]);
  async function setStatus(row: any, status: string) {
    await fetch("/api/admin/inquiries", { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id, status }) });
    setToast(`Listing marked "${status}"`);
    setTimeout(() => setToast(""), 2000);
    load();
  }
  async function remove(row: any) {
    if (!confirm("Delete this listing request?")) return;
    await fetch("/api/admin/inquiries", { method: "DELETE", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id }) });
    setOpen(null);
    load();
  }
  const payload = open ? parseListingPayload(String(open.message || "")) : null;
  return (
    <div className="app-card">
      <div className="app-card-head"><div><h2>Listings</h2><p className="app-card-sub">{items?.length ?? 0} property submissions</p></div></div>
      {items === null ? (
        <p className="app-empty">Loading…</p>
      ) : items.length === 0 ? (
        <p className="app-empty">No property listings yet. Submissions from the "List Your Property" form appear here.</p>
      ) : (
        <div style={{ overflowX: "auto" }}>
          <table className="app-table">
            <thead><tr><th>Owner</th><th>Property</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              {items.map((row) => {
                const p = parseListingPayload(String(row.message || ""));
                return (
                  <tr key={row.id} className="app-row-click" onClick={() => setOpen(row)}>
                    <td><strong>{row.name}</strong><div style={{ fontSize: 12, color: "#9399a4" }}>{row.email}{row.phone ? " · " + row.phone : ""}</div></td>
                    <td>{p ? `${p.transaction || ""} ${p.property_type || ""}${p.community ? " · " + p.community : ""}` : row.property_slug || "Property"}</td>
                    <td><span className="app-badge">{row.status}</span></td>
                    <td>{fmtDate(row.created_at)}</td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}
      {open && (
        <DetailWindow title="Listing Details" onClose={() => setOpen(null)}>
          <DetailGroup label="Owner">
            <DetailField label="Name" value={open.name} />
            <DetailField label="Email" value={open.email} />
            <DetailField label="Phone" value={open.phone} />
          </DetailGroup>
          <DetailGroup label="Property">
            <DetailField label="Transaction" value={payload?.transaction} />
            <DetailField label="Property Type" value={payload?.property_type} />
            <DetailField label="Community / Area" value={payload?.community} />
            <DetailField label="Bedrooms" value={payload?.bedrooms} />
            <DetailField label="Bathrooms" value={payload?.bathrooms} />
            <DetailField label="Size (sq ft)" value={payload?.size_sqft} />
            <DetailField label="Expected Price (AED)" value={payload?.expected_price} />
            <DetailField label="Ownership Status" value={payload?.ownership} />
            <DetailField label="Message" value={payload?.message} />
          </DetailGroup>
          <DetailGroup label="Details">
            <DetailField label="Received" value={open.created_at ? new Date(open.created_at).toLocaleString("en-GB") : ""} />
            <DetailField label="User" value={open.user_name || open.user_email} />
          </DetailGroup>
          <div className="app-detail-actions">
            <select
              style={{ border: "1px solid #e1e8ed", borderRadius: 6, fontSize: 12, padding: "6px 10px" }}
              value={open.status}
              onChange={(e) => setStatus(open, e.target.value)}
            >
              {["new", "contacted", "closed"].map((s) => <option key={s} value={s}>{s}</option>)}
            </select>
            <button type="button" className="app-btn danger sm" onClick={() => remove(open)}>Delete</button>
          </div>
        </DetailWindow>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </div>
  );
}

function ViewingsManager() {
  const [items, setItems] = useState<any[] | null>(null);
  const [open, setOpen] = useState<any | null>(null);
  const [toast, setToast] = useState("");
  const load = useCallback(() => {
    fetch("/api/admin/viewings")
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }, []);
  useEffect(load, [load]);
  async function setStatus(row: any, status: string) {
    await fetch("/api/admin/viewings", { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id, status }) });
    setToast(`Viewing marked "${status}"`);
    setTimeout(() => setToast(""), 2000);
    load();
  }
  async function remove(row: any) {
    if (!confirm("Delete this viewing?")) return;
    await fetch("/api/admin/viewings", { method: "DELETE", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: row.id }) });
    setOpen(null);
    load();
  }
  return (
    <div className="app-card">
      <div className="app-card-head"><div><h2>Viewings</h2><p className="app-card-sub">{items?.length ?? 0} requests</p></div></div>
      {items === null ? (
        <p className="app-empty">Loading…</p>
      ) : items.length === 0 ? (
        <p className="app-empty">No viewing requests yet.</p>
      ) : (
        <div style={{ overflowX: "auto" }}>
          <table className="app-table">
            <thead><tr><th>Customer</th><th>Property</th><th>Date / time</th><th>Status</th></tr></thead>
            <tbody>
              {items.map((row) => (
                <tr key={row.id} className="app-row-click" onClick={() => setOpen(row)}>
                  <td><strong>{row.user_name || row.user_email || "Guest"}</strong><div style={{ fontSize: 12, color: "#9399a4" }}>{row.name || ""}</div></td>
                  <td>{row.property_slug || row.property_ref || "General"}</td>
                  <td>{row.preferred_date}<div style={{ fontSize: 12, color: "#9399a4" }}>{row.time_slot}</div></td>
                  <td><span className="app-badge">{row.status}</span></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      {open && (
        <DetailWindow title="Viewing Details" onClose={() => setOpen(null)}>
          <DetailGroup label="Customer">
            <DetailField label="Name" value={open.name || open.user_name} />
            <DetailField label="Email" value={open.email || open.user_email} />
            <DetailField label="Phone" value={open.phone} />
          </DetailGroup>
          <DetailGroup label="Viewing">
            <DetailField label="Property" value={open.property_slug || open.property_ref} />
            <DetailField label="Date" value={open.preferred_date} />
            <DetailField label="Time" value={open.time_slot} />
            <DetailField label="Notes" value={open.notes} />
          </DetailGroup>
          <DetailGroup label="Details">
            <DetailField label="Received" value={open.created_at ? new Date(open.created_at).toLocaleString("en-GB") : ""} />
            <DetailField label="User" value={open.user_name || open.user_email} />
          </DetailGroup>
          <div className="app-detail-actions">
            <select
              style={{ border: "1px solid #e1e8ed", borderRadius: 6, fontSize: 12, padding: "6px 10px" }}
              value={open.status}
              onChange={(e) => setStatus(open, e.target.value)}
            >
              {["requested", "confirmed", "completed", "cancelled"].map((s) => <option key={s} value={s}>{s}</option>)}
            </select>
            <button type="button" className="app-btn danger sm" onClick={() => remove(open)}>Delete</button>
          </div>
        </DetailWindow>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </div>
  );
}

/* ===================== Categories & KV ===================== */

function CategoriesManager() {
  const [items, setItems] = useState<any[] | null>(null);
  const [toast, setToast] = useState("");
  const [creating, setCreating] = useState(false);
  const [editing, setEditing] = useState<any | null>(null);
  const load = useCallback(() => {
    fetch("/api/admin/categories")
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }, []);
  useEffect(load, [load]);
  async function save(row: any, body: any) {
    await fetch("/api/admin/categories" + (row?.id ? `?id=${row.id}` : ""), {
      method: row?.id ? "PUT" : "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });
    setToast(row?.id ? "Updated" : "Created");
    setTimeout(() => setToast(""), 2000);
    setCreating(false);
    setEditing(null);
    load();
  }
  async function remove(row: any) {
    if (!confirm(`Delete category "${row.name}"?`)) return;
    await fetch(`/api/admin/categories?id=${row.id}`, { method: "DELETE" });
    load();
  }
  return (
    <>
      {creating || editing ? (
        <CategoryForm
          row={editing}
          onCancel={() => { setCreating(false); setEditing(null); }}
          onSave={save}
        />
      ) : (
        <div className="app-card">
          <div className="app-card-head">
            <div><h2>Categories</h2><p className="app-card-sub">{items?.length ?? 0} entries</p></div>
            <button type="button" className="app-btn" onClick={() => setCreating(true)}>+ Add</button>
          </div>
          {items === null ? (
            <p className="app-empty">Loading…</p>
          ) : items.length === 0 ? (
            <p className="app-empty">No categories.</p>
          ) : (
            <table className="app-table">
              <thead><tr><th>Name</th><th>Slug</th><th>Type</th><th>Sort</th><th></th></tr></thead>
              <tbody>
                {items.map((row) => (
                  <tr key={row.id}>
                    <td><strong>{row.name}</strong></td>
                    <td>{row.slug}</td>
                    <td>{row.type}</td>
                    <td>{row.sort}</td>
                    <td>
                      <div className="row-actions">
                        <button type="button" className="app-btn ghost sm" onClick={() => setEditing(row)}>Edit</button>
                        <button type="button" className="app-btn danger sm" onClick={() => remove(row)}>Delete</button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </>
  );
}

function CategoryForm({ row, onCancel, onSave }: { row: any | null; onCancel: () => void; onSave: (row: any | null, body: any) => void }) {
  const [name, setName] = useState(row?.name || "");
  const [slug, setSlug] = useState(row?.slug || "");
  const [type, setType] = useState(row?.type || "");
  const [sort, setSort] = useState(row?.sort || 0);
  return (
    <div className="app-card">
      <div className="app-card-head">
        <div>
          <h2>{row ? "Edit category" : "Add category"}</h2>
        </div>
        <button type="button" className="app-btn ghost sm" onClick={onCancel}>← Back to Categories</button>
      </div>
      <form
        className="app-form-grid"
        onSubmit={(e) => {
          e.preventDefault();
          onSave(row, { name: name.trim(), slug: slug.trim(), type: type.trim(), sort: Number(sort) || 0 });
        }}
      >
        <div className="app-field full">
          <label>Name</label>
          <input value={name} onChange={(e) => setName(e.target.value)} required />
        </div>
        <div className="app-field">
          <label>Slug</label>
          <input value={slug} onChange={(e) => setSlug(e.target.value)} />
        </div>
        <div className="app-field">
          <label>Type</label>
          <input value={type} onChange={(e) => setType(e.target.value)} />
        </div>
        <div className="app-field">
          <label>Sort order</label>
          <input type="number" value={sort} onChange={(e) => setSort(e.target.value)} />
        </div>
        <div className="form-actions full">
          <button type="button" className="app-btn ghost" onClick={onCancel}>Cancel</button>
          <button type="submit" className="app-btn">Save</button>
        </div>
      </form>
    </div>
  );
}

function KVManager({ endpoint, title, defaults, selects }: { endpoint: string; title: string; defaults: string[]; selects?: Record<string, boolean> }) {
  const [values, setValues] = useState<Record<string, string>>({});
  const [toast, setToast] = useState("");
  const [busy, setBusy] = useState(false);
  useEffect(() => {
    fetch(`/api/admin/${endpoint}`)
      .then((r) => r.json())
      .then((d) => {
        const v: Record<string, string> = {};
        for (const k of defaults) v[k] = "";
        for (const it of d.items || []) v[String(it.key)] = String(it.value || "");
        setValues(v);
      })
      .catch(() => {
        const v: Record<string, string> = {};
        for (const k of defaults) v[k] = "";
        setValues(v);
      });
  }, [endpoint, defaults]);
  async function save(e: React.FormEvent) {
    e.preventDefault();
    setBusy(true);
    const res = await fetch(`/api/admin/${endpoint}`, { method: "PUT", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ items: Object.entries(values).map(([key, value]) => ({ key, value })) }) });
    setBusy(false);
    if (res.ok) {
      setToast("Saved");
      setTimeout(() => setToast(""), 2000);
    }
  }
  return (
    <div className="app-card">
      <div className="app-card-head"><div><h2>{title}</h2><p className="app-card-sub">Saved to the database for future use.</p></div></div>
      <form className="app-form-grid" onSubmit={save}>
        {Object.entries(values).map(([key, value]) => (
          <div className="app-field" key={key}>
            <label>{key.replace(/_/g, " ")}</label>
            {selects?.[key] ? (
              <select value={value} onChange={(e) => setValues({ ...values, [key]: e.target.value })}>
                <option value="">Select country</option>
                {COUNTRIES.map((c) => (
                  <option key={c.code} value={c.code}>{c.flag} {c.name} ({c.dial})</option>
                ))}
              </select>
            ) : key === "phone" && values.country ? (
              <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <CountryFlag code={values.country} />
                <input value={value} onChange={(e) => setValues({ ...values, [key]: e.target.value })} />
              </div>
            ) : (
              <input value={value} onChange={(e) => setValues({ ...values, [key]: e.target.value })} />
            )}
          </div>
        ))}
        <div className="full">
          <button type="submit" className="app-btn" disabled={busy}>{busy ? "Saving…" : "Save"}</button>
        </div>
      </form>
      {toast && <div className="app-toast">{toast}</div>}
    </div>
  );
}

/* ===================== Column renderers ===================== */

function Bool({ value }: { value: any }) {
  return <span className={"app-badge " + (Number(value) ? "active" : "inactive")}>{Number(value) ? "yes" : "no"}</span>;
}

const serviceColumns: Column[] = [
  { key: "title", label: "Service", render: (r) => <strong>{r.title}</strong> },
  { key: "slug", label: "Slug", render: (r) => <span style={{ color: "#9399a4" }}>{r.slug}</span> },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

const agentColumns: Column[] = [
  { key: "name", label: "Agent", render: (r) => <strong>{r.name}</strong> },
  { key: "role", label: "Role", render: (r) => r.role },
  { key: "email", label: "Email", render: (r) => <span style={{ fontSize: 12 }}>{r.email}</span> },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

const developerColumns: Column[] = [
  { key: "name", label: "Developer", render: (r) => <strong>{r.name}</strong> },
  { key: "region", label: "Region", render: (r) => r.region },
  { key: "founded", label: "Founded", render: (r) => r.founded || "—" },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

const communityColumns: Column[] = [
  { key: "name", label: "Community", render: (r) => <strong>{r.name}</strong> },
  { key: "region", label: "Region", render: (r) => r.region },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

const testimonialColumns: Column[] = [
  { key: "author", label: "Author", render: (r) => <strong>{r.author}</strong> },
  { key: "role", label: "Role", render: (r) => r.role },
  { key: "rating", label: "Rating", render: (r) => "★".repeat(Math.min(5, Number(r.rating) || 0)) },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

const faqColumns: Column[] = [
  { key: "question", label: "Question", render: (r) => <strong>{r.question}</strong> },
  { key: "category", label: "Category", render: (r) => r.category },
  { key: "sort", label: "Sort", render: (r) => r.sort },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

const mediaColumns: Column[] = [
  { key: "url", label: "URL", render: (r) => <span style={{ wordBreak: "break-all" }}>{r.url}</span> },
  { key: "kind", label: "Kind", render: (r) => <span className="app-badge" style={{ background: "#f0f3f8" }}>{r.kind}</span> },
  { key: "alt", label: "Alt", render: (r) => r.alt || "—" },
];

const jobColumns: Column[] = [
  { key: "title", label: "Job", render: (r) => <strong>{r.title}</strong> },
  { key: "location", label: "Location", render: (r) => r.location || "—" },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

const projectColumns: Column[] = [
  { key: "title", label: "Project", render: (r) => <strong>{r.title}</strong> },
  { key: "developer", label: "Developer", render: (r) => r.developer || "—" },
  { key: "price", label: "Price", render: (r) => Number(r.price) ? "AED " + Number(r.price).toLocaleString("en-US") : "—" },
  { key: "status", label: "Status", render: (r) => <span className="app-badge">{r.status || "—"}</span> },
  { key: "published", label: "Published", render: (r) => <Bool value={r.published} /> },
];

function fmtDate(s: string): string {
  if (!s) return "";
  const d = new Date(s);
  if (isNaN(d.getTime())) return "";
  return d.toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
}

function singular(title: string): string {
  return title.replace(/ies$/, "y").replace(/s$/, "");
}

/* ===================== Project details (curated detail pages) ===================== */

function ProjectDetailsManager({ openSlug, onBack }: { openSlug?: string | null; onBack?: () => void }) {
  const [items, setItems] = useState<any[] | null>(null);
  const [editing, setEditing] = useState<any | null>(null);
  const [form, setForm] = useState<Record<string, string>>({});
  const [toast, setToast] = useState("");
  const [busy, setBusy] = useState(false);
  const [raw, setRaw] = useState<Record<string, any>>({});

  const load = useCallback(() => {
    fetch("/api/admin/project-details")
      .then((r) => r.json())
      .then((d) => setItems(d.items || []))
      .catch(() => setItems([]));
  }, []);

  useEffect(() => {
    if (openSlug) {
      fetch(`/api/admin/project-details?slug=${encodeURIComponent(openSlug)}`)
        .then((r) => r.json())
        .then((d) => {
          const data = d.item?.data || {};
          setRaw(data);
          setForm({
            about: String(data.about || ""),
            display_price: String(data.display_price || ""),
            completion_year: String(data.completion_year || ""),
            payment_plan_text: String(data.payment_plan_text || ""),
            gallery: toLines(data.media_images, (i) => i?.url),
            amenities: toLines(data.amenities, (a) => (a?.image?.url ? `${a.text}|${a.image.url}` : String(a.text || ""))),
            floorplans: toLines(data.floor_plans, (p) => (p?.media?.url ? `${p.title}|${p.media.url}` : String(p.title || ""))),
            usp_heading: String(data.characteristics_module?.heading || ""),
            usp_title: String(data.characteristics_module?.title || ""),
            usp_description: String(data.characteristics_module?.description || ""),
            usp_image: String(data.characteristics_module?.image?.url || ""),
            loc_heading: String(data.location_tile?.heading || ""),
            loc_title: String(data.location_tile?.title || ""),
            loc_description: String(data.location_tile?.description || ""),
            loc_image: String(data.location_tile?.image?.url || ""),
            brochure_pdf: String(data.brochure?.file?.url || ""),
            brochure_cover: String(data.brochure?.image?.url || ""),
            faqs: toLines(data.more_info, (f) => `${f.question}|${String(f.answer || "").replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim()}`),
          });
          setEditing({ slug: openSlug, title: data.title || openSlug });
        });
    } else {
      load();
    }
  }, [openSlug]);

  function showToast(msg: string) {
    setToast(msg);
    setTimeout(() => setToast(""), 2200);
  }

  function toLines(arr: any[] | undefined, pick: (x: any) => string): string {
    return (arr || []).map(pick).filter(Boolean).join("\n");
  }

  async function openEdit(row: any) {
    const res = await fetch(`/api/admin/project-details?slug=${encodeURIComponent(row.slug)}`);
    const d = await res.json();
    const data = d.item?.data || {};
    setRaw(data);
    setForm({
      about: String(data.about || ""),
      display_price: String(data.display_price || ""),
      completion_year: String(data.completion_year || ""),
      payment_plan_text: String(data.payment_plan_text || ""),
      gallery: toLines(data.media_images, (i) => i?.url),
      amenities: toLines(data.amenities, (a) => (a?.image?.url ? `${a.text}|${a.image.url}` : String(a.text || ""))),
      floorplans: toLines(data.floor_plans, (p) => (p?.media?.url ? `${p.title}|${p.media.url}` : String(p.title || ""))),
      usp_heading: String(data.characteristics_module?.heading || ""),
      usp_title: String(data.characteristics_module?.title || ""),
      usp_description: String(data.characteristics_module?.description || ""),
      usp_image: String(data.characteristics_module?.image?.url || ""),
      loc_heading: String(data.location_tile?.heading || ""),
      loc_title: String(data.location_tile?.title || ""),
      loc_description: String(data.location_tile?.description || ""),
      loc_image: String(data.location_tile?.image?.url || ""),
      brochure_pdf: String(data.brochure?.file?.url || ""),
      brochure_cover: String(data.brochure?.image?.url || ""),
      faqs: toLines(data.more_info, (f) => `${f.question}|${String(f.answer || "").replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim()}`),
    });
    setEditing(row);
  }

  async function save(e: React.FormEvent) {
    e.preventDefault();
    setBusy(true);
    const data: Record<string, any> = { ...raw };
    data.about = form.about;
    data.display_price = form.display_price;
    data.completion_year = form.completion_year;
    data.payment_plan_text = form.payment_plan_text;
    data.media_images = form.gallery.split("\n").map((s) => s.trim()).filter(Boolean).map((url) => ({ url }));
    data.amenities = form.amenities.split("\n").map((s) => s.trim()).filter(Boolean).map((line) => {
      const [text, image] = line.split("|");
      return { text: (text || "").trim(), image: image ? { url: image.trim() } : null };
    });
    data.floor_plans = form.floorplans.split("\n").map((s) => s.trim()).filter(Boolean).map((line) => {
      const [title, media] = line.split("|");
      return { title: (title || "").trim(), media: media ? { url: media.trim() } : null };
    });
    data.characteristics_module = {
      ...(raw.characteristics_module || {}),
      heading: form.usp_heading,
      title: form.usp_title,
      description: form.usp_description,
      image: form.usp_image ? { url: form.usp_image } : raw.characteristics_module?.image || null,
    };
    data.location_tile = {
      ...(raw.location_tile || {}),
      heading: form.loc_heading,
      title: form.loc_title,
      description: form.loc_description,
      image: form.loc_image ? { url: form.loc_image } : raw.location_tile?.image || null,
    };
    data.brochure = {
      ...(raw.brochure || {}),
      file: form.brochure_pdf ? { ...(raw.brochure?.file || {}), url: form.brochure_pdf } : raw.brochure?.file || null,
      image: form.brochure_cover ? { ...(raw.brochure?.image || {}), url: form.brochure_cover } : raw.brochure?.image || null,
    };
    data.more_info = form.faqs.split("\n").map((s) => s.trim()).filter(Boolean).map((line) => {
      const [question, ...rest] = line.split("|");
      return { question: (question || "").trim(), answer: rest.join("|") };
    });
    const res = await fetch("/api/admin/project-details", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ slug: editing.slug, data }),
    });
    const d = await res.json().catch(() => ({}));
    if (!res.ok) {
      showToast(d.error || "Save failed");
      setBusy(false);
      return;
    }
    showToast("Saved");
    setEditing(null);
    setBusy(false);
    if (openSlug && onBack) {
      onBack();
      return;
    }
    load();
  }

  if (editing) {
    return (
      <div className="app-card">
        <div className="app-card-head">
          <div>
            <h2>{editing.title || editing.slug}</h2>
            <p className="app-card-sub">{editing.slug} — saved fields drive the public project page. Fields left in the raw import keep their scraped values.</p>
          </div>
          <button type="button" className="app-btn ghost sm" onClick={() => (openSlug && onBack ? onBack() : setEditing(null))}>{openSlug && onBack ? "← Back to Projects" : "← Back to Project Details"}</button>
        </div>
        <form className="app-form-grid" onSubmit={save}>
          <div className="app-field full">
            <label>About the project (HTML allowed)</label>
            <textarea rows={5} value={form.about} onChange={(e) => setForm({ ...form, about: e.target.value })} />
          </div>
          <div className="app-field"><label>Display price (e.g. 1.96M)</label><input value={form.display_price} onChange={(e) => setForm({ ...form, display_price: e.target.value })} /></div>
          <div className="app-field"><label>Completion year</label><input value={form.completion_year} onChange={(e) => setForm({ ...form, completion_year: e.target.value })} /></div>
          <div className="app-field"><label>Payment plan text (e.g. 80/20)</label><input value={form.payment_plan_text} onChange={(e) => setForm({ ...form, payment_plan_text: e.target.value })} /></div>
          <div className="app-field full">
            <label>Gallery images (one URL per line)</label>
            <FileField textarea value={form.gallery} onValue={(v) => setForm({ ...form, gallery: v })} />
          </div>
          <div className="app-field full">
            <label>Amenities (one per line: Name|Image URL)</label>
            <FileField textarea value={form.amenities} onValue={(v) => setForm({ ...form, amenities: v })} />
          </div>
          <div className="app-field full">
            <label>Floor plans (one per line: Title|Image URL)</label>
            <FileField textarea value={form.floorplans} onValue={(v) => setForm({ ...form, floorplans: v })} />
          </div>
          <div className="app-field full"><label>USP heading</label><input value={form.usp_heading} onChange={(e) => setForm({ ...form, usp_heading: e.target.value })} /></div>
          <div className="app-field full"><label>USP title</label><input value={form.usp_title} onChange={(e) => setForm({ ...form, usp_title: e.target.value })} /></div>
          <div className="app-field full">
            <label>USP description (HTML allowed)</label>
            <textarea rows={5} value={form.usp_description} onChange={(e) => setForm({ ...form, usp_description: e.target.value })} />
          </div>
          <div className="app-field full"><label>USP image URL</label><FileField value={form.usp_image} onValue={(v) => setForm({ ...form, usp_image: v })} /></div>
          <div className="app-field full"><label>Location heading</label><input value={form.loc_heading} onChange={(e) => setForm({ ...form, loc_heading: e.target.value })} /></div>
          <div className="app-field full"><label>Location title</label><input value={form.loc_title} onChange={(e) => setForm({ ...form, loc_title: e.target.value })} /></div>
          <div className="app-field full">
            <label>Location description (HTML allowed)</label>
            <textarea rows={5} value={form.loc_description} onChange={(e) => setForm({ ...form, loc_description: e.target.value })} />
          </div>
          <div className="app-field full"><label>Location image URL</label><FileField value={form.loc_image} onValue={(v) => setForm({ ...form, loc_image: v })} /></div>
          <div className="app-field full"><label>Brochure PDF URL</label><input value={form.brochure_pdf} onChange={(e) => setForm({ ...form, brochure_pdf: e.target.value })} /></div>
          <div className="app-field full"><label>Brochure cover image URL</label><FileField value={form.brochure_cover} onValue={(v) => setForm({ ...form, brochure_cover: v })} /></div>
          <div className="app-field full">
            <label>FAQ (one per line: Question|Answer)</label>
            <textarea rows={6} value={form.faqs} onChange={(e) => setForm({ ...form, faqs: e.target.value })} />
          </div>
          <div className="app-field full">
            <button type="button" className="app-btn ghost" onClick={() => (openSlug && onBack ? onBack() : setEditing(null))}>Cancel</button>
            <button type="submit" className="app-btn" disabled={busy}>{busy ? "Saving…" : "Save"}</button>
          </div>
        </form>
        {toast && <div className="app-toast">{toast}</div>}
      </div>
    );
  }

  return (
    <div className="app-card">
      <div className="app-card-head">
        <div>
          <h2>Project Details</h2>
          <p className="app-card-sub">{items?.length ?? 0} projects with rich detail pages</p>
        </div>
      </div>
      {items == null ? (
        <p className="app-empty">Loading…</p>
      ) : items.length === 0 ? (
        <p className="app-empty">No project details yet.</p>
      ) : (
        <table className="app-table">
          <thead>
            <tr><th>Project</th><th>Developer</th><th>Location</th><th>Completion</th><th>Updated</th><th></th></tr>
          </thead>
          <tbody>
            {items.map((row) => (
              <tr key={row.slug} className="app-row-click" onClick={(e) => onRowOpen(e, rowUrl("projects", row))}>
                <td><strong>{row.title || row.slug}</strong><div style={{ fontSize: 12, color: "#9399a4" }}>{row.slug}</div></td>
                <td>{row.developer || "—"}</td>
                <td>{row.display_address || "—"}</td>
                <td>{row.completion_year || "—"}</td>
                <td>{fmtDate(row.updated_at)}</td>
                <td>
                  <button type="button" className="app-btn ghost sm" onClick={() => openEdit(row)}>Edit</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
      {toast && <div className="app-toast">{toast}</div>}
    </div>
  );
}
