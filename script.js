let fabTimeout;
const fabContainer = document.getElementById("dev-fab-container");

function resetFabTimer() {
  fabContainer.classList.remove("hidden-fab");
  clearTimeout(fabTimeout);

  fabTimeout = setTimeout(() => {
    fabContainer.classList.add("hidden-fab");
  }, 2000);
}

document.addEventListener("mousemove", resetFabTimer);
document.addEventListener("click", resetFabTimer);

resetFabTimer();

function toggleDevMenu() {
  const menu = document.getElementById("dev-fab-menu");
  menu.classList.toggle("show");
}

function toggleAdminMenu() {
  const menu = document.getElementById("admin-side-menu");
  menu.classList.toggle("open");
}

function toggleStudentMenu() {
  const menu = document.getElementById("student-side-menu");
  menu.classList.toggle("open");
}

function toggleProfMenu() {
  const menu = document.getElementById("prof-side-menu");
  menu.classList.toggle("open");
}

function switchRole(role) {
  document.getElementById("view-admin").style.display = "none";
  document.getElementById("view-prof").style.display = "none";
  document.getElementById("view-student").style.display = "none";
  document.getElementById("view-login").style.display = "none";

  if (role === "admin") {
    document.getElementById("view-admin").style.display = "flex";
    switchAdminTab(
      "dashboard",
      document.querySelector(".admin-sidebar li:nth-child(1)"),
    );
  } else if (role === "login") {
    document.getElementById("view-" + role).style.display = "flex";
  } else {
    document.getElementById("view-" + role).style.display = "block";

    if (role === "student")
      switchStudentTab(
        "inicio",
        document.querySelector("#view-student .mobile-nav button:nth-child(1)"),
      );
    if (role === "prof")
      switchProfTab(
        "generar",
        document.querySelector("#view-prof .mobile-nav button:nth-child(1)"),
      );
  }
}

function switchAdminTab(tabId, element) {
  document.getElementById("admin-dashboard").classList.add("hidden");
  document.getElementById("admin-management").classList.add("hidden");
  document.getElementById("admin-reports").classList.add("hidden");
  document.getElementById("admin-password").classList.add("hidden");

  document.getElementById("admin-" + tabId).classList.remove("hidden");

  const items = document.querySelectorAll(".admin-sidebar li");
  items.forEach((li) => li.classList.remove("active"));
  if (element) {
    element.classList.add("active");
  }

  if (tabId === "reports") {
    backToAdminReports();
  }
}

function viewAdminReportDetail(course, week) {
  document.getElementById("admin-reports-list").classList.add("hidden");
  document.getElementById("admin-reports-detail").classList.remove("hidden");
  document.getElementById("admin-report-title").innerText =
    course + " - " + week;
}

function backToAdminReports() {
  document.getElementById("admin-reports-list").classList.remove("hidden");
  document.getElementById("admin-reports-detail").classList.add("hidden");
}

function switchProfTab(tabId, element) {
  document.getElementById("prof-generar").classList.add("hidden");
  document.getElementById("prof-asistencia").classList.add("hidden");
  document.getElementById("prof-ajustes").classList.add("hidden");
  document.getElementById("prof-password").classList.add("hidden");
  document.getElementById("prof-diagnostico").classList.add("hidden");

  document.getElementById("prof-" + tabId).classList.remove("hidden");

  const btns = document.querySelectorAll("#view-prof .mobile-nav button");
  btns.forEach((btn) => btn.classList.remove("active"));
  if (element) {
    element.classList.add("active");
  }

  if (tabId === "asistencia") {
    backToClasses();
  }
}

function viewAttendance(course, week) {
  document.getElementById("prof-classes-list").classList.add("hidden");
  document.getElementById("prof-attendance-detail").classList.remove("hidden");
  document.getElementById("attendance-detail-title").innerText =
    course + " - " + week;
}

function backToClasses() {
  document.getElementById("prof-classes-list").classList.remove("hidden");
  document.getElementById("prof-attendance-detail").classList.add("hidden");
}

function generateQR() {
  document.getElementById("qr-display-area").classList.remove("hidden");
}

function closeQR() {
  document.getElementById("qr-display-area").classList.add("hidden");
}

function saveSettings() {
  const popup = document.getElementById("settings-popup");
  popup.classList.add("show");

  setTimeout(() => {
    popup.classList.remove("show");
  }, 3000);
}

function updatePassword(role) {
  const popup = document.getElementById(role + "-password-popup");
  popup.classList.add("show");

  setTimeout(() => {
    popup.classList.remove("show");
    if (role === "admin") {
      switchAdminTab(
        "dashboard",
        document.querySelector(".admin-sidebar li:nth-child(1)"),
      );
    } else if (role === "prof") {
      switchProfTab(
        "generar",
        document.querySelector("#view-prof .mobile-nav button:nth-child(1)"),
      );
    } else if (role === "student") {
      switchStudentTab(
        "inicio",
        document.querySelector("#view-student .mobile-nav button:nth-child(1)"),
      );
    }
  }, 1500);
}

function resetUserPassword() {
  const popup = document.getElementById("admin-reset-popup");
  popup.classList.add("show");

  setTimeout(() => {
    popup.classList.remove("show");
  }, 9000);
}

function filterLogs() {
  const filter = document.getElementById("log-filter").value;
  const logs = document.querySelectorAll("#diagnostics-log-list li");

  logs.forEach((log) => {
    if (filter === "all" || log.getAttribute("data-type") === filter) {
      log.style.display = "block";
    } else {
      log.style.display = "none";
    }
  });
}

function activatePanicMode() {
  const popup = document.getElementById("panic-popup");
  popup.classList.add("show");
  setTimeout(() => {
    popup.classList.remove("show");
  }, 4000);
}

function switchStudentTab(tabId, element) {
  document.getElementById("student-inicio").classList.add("hidden");
  document.getElementById("student-escanear").classList.add("hidden");
  document.getElementById("student-historial").classList.add("hidden");
  document.getElementById("student-guia").classList.add("hidden");
  document.getElementById("student-password").classList.add("hidden");

  document.getElementById("student-" + tabId).classList.remove("hidden");

  const btns = document.querySelectorAll("#view-student .mobile-nav button");
  btns.forEach((btn) => btn.classList.remove("active"));
  if (element) {
    element.classList.add("active");
  }
}

function simularEscaneo() {
  const popup = document.getElementById("scan-popup");

  popup.classList.add("show");

  setTimeout(() => {
    popup.classList.remove("show");
  }, 1500);
}
