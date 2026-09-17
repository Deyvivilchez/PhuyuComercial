(function () {
  const shouldLoadPlugins =
    document.querySelector("[toast-list]") ||
    document.querySelector("[data-choices]") ||
    document.querySelector("[data-provider]");

  if (!shouldLoadPlugins) {
    return;
  }

  const scripts = [
    "/sistemas/phuyu_comercial/public/plantilla_phuyu/js/toastify-js.js",
    "/sistemas/phuyu_comercial/public/plantilla_phuyu/libs/choices.js/public/assets/scripts/choices.min.js",
    "/sistemas/phuyu_comercial/public/plantilla_phuyu/libs/flatpickr/flatpickr.min.js"
  ];

  scripts.forEach(function (src) {
    if (document.querySelector('script[src="' + src + '"]')) {
      return;
    }

    document.writeln(
      '<script type="text/javascript" src="' + src + '"><\/script>'
    );
  });
})();