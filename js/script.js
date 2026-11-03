/* DreamBrixs interactions. Update the WhatsApp number in this file before launch. */
(() => {
  "use strict";
  const WHATSAPP_NUMBER = "919840000000"; // Replace with the business WhatsApp number, digits only.
  const WHATSAPP_GREETING = "Hello DreamBrixs, I would like to enquire about a residential construction project.";
  const makeWhatsAppUrl = (message) => `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;

  const floatingWhatsapp = document.getElementById("floatingWhatsapp");
  const formWhatsapp = document.getElementById("formWhatsapp");
  const form = document.getElementById("enquiryForm");
  const formNote = document.getElementById("formNote");
  const backToTop = document.getElementById("backToTop");
  const year = document.getElementById("year");

  if (floatingWhatsapp) {
    floatingWhatsapp.href = makeWhatsAppUrl(WHATSAPP_GREETING);
    floatingWhatsapp.target = "_blank";
    floatingWhatsapp.rel = "noopener noreferrer";
  }

  function getFormMessage() {
    if (!form) return WHATSAPP_GREETING;
    const data = new FormData(form);
    const fields = [
      ["Name", data.get("name")],
      ["Email", data.get("email")],
      ["Phone", data.get("phone")],
      ["Location", data.get("location")],
      ["Project type", data.get("projectType")],
      ["Project details", data.get("message")]
    ].filter(([, value]) => String(value || "").trim());
    return `Hello DreamBrixs, I would like to enquire about a residential construction project.\n\n${fields.map(([label, value]) => `${label}: ${String(value).trim()}`).join("\n")}`;
  }

  if (formWhatsapp) {
    formWhatsapp.addEventListener("click", () => {
      window.open(makeWhatsAppUrl(getFormMessage()), "_blank", "noopener,noreferrer");
    });
  }

  if (form) {
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const message = getFormMessage();
      if (formNote) formNote.textContent = "Opening WhatsApp with your enquiry. Please press Send in WhatsApp to contact DreamBrixs.";
      window.open(makeWhatsAppUrl(message), "_blank", "noopener,noreferrer");
    });
  }

  if (year) year.textContent = String(new Date().getFullYear());

  const updateBackToTop = () => {
    if (backToTop) backToTop.classList.toggle("show", window.scrollY > 500);
  };
  window.addEventListener("scroll", updateBackToTop, { passive: true });
  updateBackToTop();
  if (backToTop) backToTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

  // Close the mobile navigation after a menu item is selected.
  document.querySelectorAll("#mainMenu .nav-link").forEach((link) => {
    link.addEventListener("click", () => {
      const menu = document.getElementById("mainMenu");
      if (menu && menu.classList.contains("show") && window.bootstrap) {
        window.bootstrap.Collapse.getOrCreateInstance(menu).hide();
      }
    });
  });
})();
