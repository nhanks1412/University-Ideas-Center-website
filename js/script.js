/* =========================================================
   CORE JAVASCRIPT - UNI IDEA HUB
   ========================================================= */

// 1. Handle Page Close (Used for Terms, Privacy...)
function closeTerms() {
  // Try to close the tab if opened as a popup
  try {
    window.close();
  } catch (e) {
    console.log("Cannot close tab: " + e);
  }

  // Wait 100ms, if the tab is not closed, return to the homepage or the previous page
  setTimeout(function () {
    if (window.history.length > 1) {
      window.history.back(); // Go back to the previous page
    } else {
      window.location.href = "index.php"; // Go to homepage
    }
  }, 100);
}

// 2. Automatically hide alerts after 5 seconds
document.addEventListener("DOMContentLoaded", function () {
  setTimeout(function () {
    let alerts = document.querySelectorAll(".alert-dismissible");
    alerts.forEach(function (alert) {
      // Use Bootstrap API to close alert (if bootstrap.js is available)
      // Or hide manually
      alert.classList.remove("show");
      alert.classList.add("fade");
      setTimeout(() => alert.remove(), 500); // Remove from DOM after fading out
    });
  }, 5000); // 5000ms = 5 seconds
});

// 3. HANDLE VOTE FUNCTION (LIKE/DISLIKE)
async function voteIdea(ideaId, voteType) {
  try {
    // Create data to send
    const formData = new FormData();
    formData.append("idea_id", ideaId);
    formData.append("vote_type", voteType);

    // Call vote_idea.php API
    const response = await fetch("vote_idea.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.status === "success") {
      // Update the interface data immediately
      // Find the span containing the like/dislike count and change its content
      document.getElementById(`upvote-count-${ideaId}`).innerText =
        data.upvotes;
      document.getElementById(`downvote-count-${ideaId}`).innerText =
        data.downvotes;
    } else {
      alert(data.message);
    }
  } catch (error) {
    console.error("Error:", error);
  }
}
