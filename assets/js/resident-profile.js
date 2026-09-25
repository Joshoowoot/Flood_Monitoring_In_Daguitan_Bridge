(() => {
  const button = document.getElementById("profileImageButton");
  const input = document.getElementById("profileImageInput");
  const image = document.getElementById("profileImage");
  const storageKey = "daguitanResidentProfileImage";

  if (!button || !input || !image) return;

  try {
    const savedImage = window.localStorage.getItem(storageKey);
    if (savedImage) image.src = savedImage;
  } catch (error) {}

  button.addEventListener("click", () => input.click());
  input.addEventListener("change", () => {
    const file = input.files && input.files[0];
    if (!file || !file.type.startsWith("image/")) return;

    const reader = new FileReader();
    reader.addEventListener("load", () => {
      if (typeof reader.result !== "string") return;
      image.src = reader.result;
      try {
        window.localStorage.setItem(storageKey, reader.result);
      } catch (error) {}
    });
    reader.readAsDataURL(file);
  });
})();
