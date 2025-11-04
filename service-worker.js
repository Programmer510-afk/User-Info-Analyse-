const CACHE_NAME = 'user-app-cache-v2'; // ক্যাশ ভার্সন আপডেট করা হয়েছে
const urlsToCache = [
  './',
  'index.php',
  'dashboard.php',
  'search.php',
  'signup.php',
  'print_report.php',
  'data.php', 
  'style.css',
  'service-worker.js',
  'manifest.json',
  'images/hide.svg',
  'images/show.svg',
  'images/icon-192x192.png',
  'images/icon-512x512.png'
];

// ইনস্টলেশন ইভেন্ট: সব প্রয়োজনীয় ফাইল ক্যাশ করুন
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache).catch(error => {
            console.error('Cache addAll failed:', error); // কোন ফাইল লোড না হলে এরর দেখাবে
        });
      })
  );
});

// সক্রিয়করণ ইভেন্ট: পুরোনো ক্যাশ মুছে ফেলুন
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// ফেচ ইভেন্ট: অফলাইনে থাকলে ক্যাশ থেকে ফাইল পরিবেশন করুন
self.addEventListener('fetch', event => {
  // POST রিকোয়েস্ট (যেমন ডেটা সেভ করা) ক্যাশ করার প্রয়োজন নেই
  if (event.request.method !== 'POST') {
      event.respondWith(
        caches.match(event.request)
          .then(response => {
            if (response) {
              return response;
            }
            return fetch(event.request);
          })
      );
  }
});