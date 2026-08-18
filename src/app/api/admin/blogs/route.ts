import { NextResponse } from "next/server";
import { requireAdmin } from "@/server/session";
import { readFileSync, readdirSync, existsSync } from "node:fs";
import path from "node:path";

const DIR = path.join(process.cwd(), "data", "raw", "pages", "blog");

export async function GET(req: Request) {
  await requireAdmin();
  const { searchParams } = new URL(req.url);
  const q = (searchParams.get("q") || "").toLowerCase();
  const items: any[] = [];
  if (existsSync(DIR)) {
    for (const f of readdirSync(DIR)) {
      if (!f.endsWith(".json")) continue;
      try {
        const j = JSON.parse(readFileSync(path.join(DIR, f), "utf8"));
        const b = j?.result?.data?.strapiBlog;
        if (!b) continue;
        const rawCat = b.category;
        const cat =
          rawCat && typeof rawCat === "object" && rawCat !== null
            ? Array.isArray(rawCat.strapi_json_value)
              ? rawCat.strapi_json_value.join(", ")
              : ""
            : String(rawCat || "");
        items.push({
          title: String(b.title || f.replace(/\.json$/, "")),
          slug: String(b.slug || f.replace(/\.json$/, "")),
          category: cat,
          author: String(b.author || ""),
          date: String(b.date || ""),
          published: b.publish ? 1 : 0,
        });
      } catch {
        /* skip unparseable file */
      }
    }
    items.sort((a, b2) => {
      const ta = Date.parse(a.date) || 0;
      const tb = Date.parse(b2.date) || 0;
      return tb - ta;
    });
    if (q) {
      return NextResponse.json({
        items: items.filter(
          (i) =>
            i.title.toLowerCase().includes(q) ||
            i.slug.toLowerCase().includes(q) ||
            i.category.toLowerCase().includes(q)
        ),
      });
    }
  }
  return NextResponse.json({ items });
}