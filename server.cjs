/* cPanel/Node.js-selector entry point.
   Runs the built Next.js app (`next build` output in .next) without a
   terminal: `node server.js`. Listens on PORT (set by the host) or 3000. */
const { createServer } = require("http");
const next = require("next");

const port = Number(process.env.PORT) || 3000;
const hostname = "0.0.0.0";
const app = next({ dev: false, hostname, port, dir: __dirname });

app
  .prepare()
  .then(() => {
    const handle = app.getRequestHandler();
    createServer((req, res) => handle(req, res))
      .listen(port, hostname, () => {
        console.log(`> Zoya Ventures Next.js ready on http://${hostname}:${port}`);
      });
  })
  .catch((err) => {
    console.error("FATAL: Next.js failed to start:", err);
    process.exit(1);
  });
