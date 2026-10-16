(() => {
  "use strict";

  const form = document.getElementById("familyPlanForm");
  const status = document.getElementById("familyPlanStatus");
  const review = document.getElementById("familyPlanReview");
  const clearButton = document.getElementById("familyPlanClear");
  const printButton = document.getElementById("familyPlanPrint");
  const downloadButton = document.getElementById("familyPlanDownload");
  const shareButton = document.getElementById("familyPlanShare");
  const revokeButton = document.getElementById("familyPlanRevoke");
  const consent = document.getElementById("familyPlanConsent");
  const shareStatus = document.getElementById("familyPlanShareStatus");
  if (!form || !status || !review || !clearButton || !printButton || !downloadButton
    || !shareButton || !revokeButton || !consent || !shareStatus) return;

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

  const request = async (url, values) => {
    const body = new URLSearchParams();
    body.set("family_plan_token", form.dataset.shareToken || "");
    Object.entries(values).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((item) => body.append(`${key}[]`, item));
      } else {
        body.set(key, value);
      }
    });
    const response = await fetch(url, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
      body: body.toString(),
    });
    const result = await response.json();
    if (!response.ok || !result.ok) {
      throw new Error(result.error || "The request could not be completed.");
    }
    return result;
  };

  const refreshShareStatus = async () => {
    try {
      const response = await fetch(form.dataset.statusUrl, { credentials: "same-origin" });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error("Could not check plan-sharing status.");
      revokeButton.hidden = !result.shared;
      if (result.shared) {
        const sharedDate = result.shared_at ? formatDate(new Date(result.shared_at.replace(" ", "T"))) : "";
        shareStatus.textContent = `A copy is currently shared with MDRRMO${sharedDate ? ` (shared ${sharedDate})` : ""}. Sharing remains active until you revoke it.`;
      } else {
        shareStatus.textContent = "No plan copy is currently shared with MDRRMO.";
      }
    } catch (error) {
      shareStatus.textContent = "Could not check sharing status. Refresh the page and try again.";
    }
  };

  const markPlanEdited = () => {
    status.textContent = "You have unsaved changes to this plan.";
    if (!revokeButton.hidden) {
      shareStatus.textContent = "Your shared copy has not changed. Select the consent box and share again to send these edits to MDRRMO.";
    }
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

  form.addEventListener("input", (event) => {
    if (event.target !== consent) markPlanEdited();
  });
  form.addEventListener("change", (event) => {
    if (event.target !== consent) markPlanEdited();
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
      if (!revokeButton.hidden) {
        shareStatus.textContent = "Your shared copy has not changed. Select the consent box and share again to send these edits to MDRRMO.";
      }
    } catch (error) {
      renderReviewReminder();
      status.textContent = "Could not save the family plan. Browser storage may be unavailable or full.";
    }
  });

  shareButton.addEventListener("click", async () => {
    if (!consent.checked) {
      shareStatus.textContent = "Check the sharing consent box before sharing your plan.";
      consent.focus();
      return;
    }
    shareButton.disabled = true;
    shareStatus.textContent = "Sharing your plan securely with MDRRMO…";
    try {
      const plan = readPlan();
      const result = await request(form.dataset.shareUrl, {
        consent: "1",
        meetingPoint: plan.meetingPoint,
        evacuationPlace: plan.evacuationPlace,
        contactName: plan.contactName,
        contactPhone: plan.contactPhone,
        supportNeeds: plan.supportNeeds,
        lastReviewedAt: plan.lastReviewedAt,
      });
      revokeButton.hidden = false;
      shareStatus.textContent = `Your plan was shared with MDRRMO on ${formatDate(new Date(result.shared_at.replace(" ", "T")))}. You can revoke sharing at any time.`;
      try {
        window.localStorage.setItem(storageKey, JSON.stringify(plan));
        status.textContent = "Family plan shared with MDRRMO and saved in this browser on this device.";
      } catch (storageError) {
        status.textContent = "Your plan was shared with MDRRMO, but could not be saved in this browser. Browser storage may be unavailable or full.";
      }
    } catch (error) {
      shareStatus.textContent = error.message || "The plan could not be shared. Please try again.";
    } finally {
      shareButton.disabled = false;
    }
  });

  revokeButton.addEventListener("click", async () => {
    if (!window.confirm("Remove the copy shared with MDRRMO? Your browser-saved plan will remain on this device.")) return;
    revokeButton.disabled = true;
    shareStatus.textContent = "Removing the shared copy…";
    try {
      await request(form.dataset.revokeUrl, {});
      revokeButton.hidden = true;
      consent.checked = false;
      shareStatus.textContent = "The shared copy was removed from MDRRMO access. Your browser-saved plan remains on this device.";
    } catch (error) {
      shareStatus.textContent = error.message || "The shared copy could not be removed. Please try again.";
    } finally {
      revokeButton.disabled = false;
    }
  });

  clearButton.addEventListener("click", () => {
    form.reset();
    try {
      window.localStorage.removeItem(storageKey);
      lastReviewedAt = "";
      renderReviewReminder();
      status.textContent = !revokeButton.hidden
        ? "Saved family plan cleared from this browser. The shared MDRRMO copy remains until you revoke sharing."
        : "Saved family plan cleared from this browser.";
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
  refreshShareStatus();
})();
