/*
  SHARED ANALYTICS / SEARCH CONSOLE SCRIPT
  Add approved tracking snippets here so they are loaded across the whole website.
  1) Google Analytics 4: replace G-XXXXXXXXXX with your Measurement ID.
  2) Google Tag Manager: use the official container snippet if your site uses GTM.
  3) Google Search Console verification is normally added as a meta tag in index.html
     or verified via DNS. Place the meta tag inside <head>, not in this JS file.

  Example GA4 loader (uncomment and replace the ID):
  (function () {
    const measurementId = "G-XXXXXXXXXX";
    if (!measurementId || measurementId === "G-XXXXXXXXXX") return;
    const script = document.createElement("script");
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${measurementId}`;
    document.head.appendChild(script);
    window.dataLayer = window.dataLayer || [];
    function gtag(){ window.dataLayer.push(arguments); }
    window.gtag = gtag;
    gtag("js", new Date());
    gtag("config", measurementId);
  })();
*/
