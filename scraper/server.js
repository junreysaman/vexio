'use strict';

const fs = require('fs');
const http = require('http');
const path = require('path');
const handler = require('./api/index.js');

const root = __dirname;
const port = Number(process.env.PORT || 3000);
const host = process.env.HOST || '127.0.0.1';

const contentTypes = {
  '.html': 'text/html; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.gif': 'image/gif',
  '.png': 'image/png',
  '.svg': 'image/svg+xml; charset=utf-8',
  '.wasm': 'application/wasm',
};

function send(res, statusCode, body, contentType = 'text/plain; charset=utf-8') {
  res.statusCode = statusCode;
  res.setHeader('Content-Type', contentType);
  res.end(body);
}

function serveStatic(req, res) {
  const requestUrl = new URL(req.url || '/', `http://${host}:${port}`);
  const pathname = requestUrl.pathname === '/' ? '/index.html' : requestUrl.pathname;
  const decodedPath = decodeURIComponent(pathname);
  const filePath = path.resolve(root, '.' + decodedPath);

  if (!filePath.startsWith(root + path.sep)) {
    return send(res, 403, 'Forbidden');
  }

  fs.readFile(filePath, (error, data) => {
    if (error) {
      return send(res, error.code === 'ENOENT' ? 404 : 500, error.code === 'ENOENT' ? 'Not found' : 'Server error');
    }

    res.statusCode = 200;
    res.setHeader('Content-Type', contentTypes[path.extname(filePath).toLowerCase()] || 'application/octet-stream');
    res.setHeader('Cache-Control', 'no-store');
    res.end(data);
  });
}

const server = http.createServer((req, res) => {
  if ((req.url || '').startsWith('/api')) {
    handler(req, res).catch((error) => {
      send(res, 500, JSON.stringify({ error: error.message }), 'application/json; charset=utf-8');
    });
    return;
  }

  serveStatic(req, res);
});

server.on('error', (error) => {
  if (error.code === 'EADDRINUSE') {
    console.error(`Port ${port} is already in use. Set PORT=3001 or stop the existing scraper server.`);
    process.exit(1);
  }

  throw error;
});

server.listen(port, host, () => {
  console.log(`Scraper running at http://${host}:${port}`);
});
