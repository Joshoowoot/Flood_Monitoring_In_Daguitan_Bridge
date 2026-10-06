(() => {
  "use strict";

  const form = document.getElementById("familyPlanForm");
  const status = document.getElementById("familyPlanStatus");
  const review = document.getElementById("familyPlanReview");
  const clearButton = document.getElementById("familyPlanClear");
  const printButton = document.getElementById("familyPlanPrint");
  const downloadButton = document.getElementById("familyPlanDownload");
  if (!form || !status || !review || !clearButton || !printButton || !downloadButton) return;

  const storageKey = "daguitanFamilyEvacuationPlan";
  const reviewIntervalMs = 90 * 24 * 60 * 60 * 1000;
  const textFields = ["meetingPoint", "evacuationPlace", "contactName", "contactPhone"];
  const supportOptions = [...form.querySelectorAll('input[name="supportNeeds"]')];
  let lastReviewedAt = "";

  const formatDate = (date) => new Intl.DateTimeFormat(undefined, {
    year: "numeric",
    month: "long",
    day: "numeric",
  }).format(date);

  const renderReviewReminder = () => {
    review.dataset.state = "current";
    if (!lastReviewedAt) {
      review.textContent = "Save your plan after reviewing it with your household to start the 90-day review reminder. Update it sooner whenever contacts, destinations, or support needs change.";
      return;
    }

    const reviewedDate = new Date(lastReviewedAt);
    if (Number.isNaN(reviewedDate.getTime())) {
      review.dataset.state = "invalid";
      review.textContent = "The saved review date is invalid. Save your plan again after reviewing it with your household.";
      return;
    }

    const dueDate = new Date(reviewedDate.getTime() + reviewIntervalMs);
    const due = Date.now() >= dueDate.getTime();
    review.dataset.state = due ? "due" : "current";
    review.textContent = due
      ? `Plan review is due. It was last reviewed ${formatDate(reviewedDate)}; review and save it again. Update sooner whenever contacts, destinations, or support needs change.`
      : `Last reviewed ${formatDate(reviewedDate)}. Review again by ${formatDate(dueDate)}, or sooner whenever contacts, destinations, or support needs change.`;
  };

  const setPlan = (plan) => {
    textFields.forEach((name) => {
      const field = form.elements.namedItem(name);
      field.value = typeof plan[name] === "string" ? plan[name].slice(0, field.maxLength) : "";
    });
    const selected = Array.isArray(plan.supportNeeds) ? plan.supportNeeds : [];
    supportOptions.forEach((option) => {
      option.checked = selected.includes(option.value);
    });
    lastReviewedAt = typeof plan.lastReviewedAt === "string" ? plan.lastReviewedAt : "";
    renderReviewReminder();
  };

  const readPlan = () => {
    const plan = {};
    textFields.forEach((name) => {
      plan[name] = form.elements.namedItem(name).value.trim();
    });
    plan.supportNeeds = supportOptions.filter((option) => option.checked).map((option) => option.value);
    plan.lastReviewedAt = lastReviewedAt;
    return plan;
  };

  try {
    const savedPlan = window.localStorage.getItem(storageKey);
    if (savedPlan !== null) {
      const parsedPlan = JSON.parse(savedPlan);
      if (!parsedPlan || typeof parsedPlan !== "object" || Array.isArray(parsedPlan)) {
        throw new Error("Saved plan data has an invalid format.");
      }
      setPlan(parsedPlan);
      status.textContent = "Saved family plan loaded from this browser.";
    }
  } catch (error) {
    status.textContent = "Could not load the saved family plan. Browser storage may be unavailable or the saved data may be damaged.";
  }

  form.addEventListener("input", () => {
    status.textContent = "You have unsaved changes to this plan.";
  });
  form.addEventListener("change", () => {
    status.textContent = "You have unsaved changes to this plan.";
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    try {
      const plan = readPlan();
      const reviewedAt = new Date().toISOString();
      plan.lastReviewedAt = reviewedAt;
      window.localStorage.setItem(storageKey, JSON.stringify(plan));
      lastReviewedAt = reviewedAt;
      renderReviewReminder();
      status.textContent = "Family plan saved in this browser on this device.";
    } catch (error) {
      renderReviewReminder();
      status.textContent = "Could not save the family plan. Browser storage may be unavailable or full.";
    }
  });

  clearButton.addEventListener("click", () => {
    form.reset();
    try {
      window.localStorage.removeItem(storageKey);
      lastReviewedAt = "";
      renderReviewReminder();
      status.textContent = "Saved family plan cleared from this browser.";
    } catch (error) {
      status.textContent = "The form was cleared, but browser storage could not remove the saved plan.";
    }
  });

  downloadButton.addEventListener("click", () => {
    try {
      const plan = readPlan();
      const reviewed = plan.lastReviewedAt ? formatDate(new Date(plan.lastReviewedAt)) : "Not yet reviewed";
      const supportNeeds = plan.supportNeeds.length ? plan.supportNeeds.map((item) => `- ${item}`).join("\n") : "- None listed";
      const contents = [
        "FAMILY EVACUATION PLAN",
        `Downloaded: ${formatDate(new Date())}`,
        `Last reviewed: ${reviewed}`,
        "",
        `Family meeting point: ${plan.meetingPoint || "Not set"}`,
        `Evacuation destination: ${plan.evacuationPlace || "Not set"}`,
        `Out-of-area contact: ${plan.contactName || "Not set"}`,
        `Contact phone: ${plan.contactPhone || "Not set"}`,
        "",
        "Household members who may need extra help:",
        supportNeeds,
        "",
        "Keep this copy private. Follow current instructions from MDRRMO and barangay officials.",
      ].join("\n");
      const file = new Blob([contents], { type: "text/plain;charset=utf-8" });
      const downloadUrl = URL.createObjectURL(file);
      const link = document.createElement("a");
      link.href = downloadUrl;
      link.download = "family-evacuation-plan.txt";
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 0);
      status.textContent = "A text copy of the current plan was downloaded. Keep it private.";
    } catch (error) {
      status.textContent = "Could not create a download copy in this browser. Use Print plan instead.";
    }
  });

  printButton.addEventListener("click", () => window.print());
})();
