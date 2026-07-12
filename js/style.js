var darkMode;
function toggleDark(){
  document.body.classList.toggle("dark");

  if(document.body.classList.contains("dark")){
    localStorage.setItem("theme","dark");
    darkMode = true;
  }else{
    localStorage.setItem("theme","light");
    darkMode = false;
  }

}

function toggleMenu(){
  const dropdown = document.getElementById("dropdown");
  const burger = document.getElementById("hamburger");

  dropdown.classList.toggle("show");
  burger.classList.toggle("active");

  burger.innerHTML = burger.classList.contains("active")
    ? '<i data-lucide="x" class="lucide-ic"></i>'
    : '<i data-lucide="menu" class="lucide-ic"></i>';
  if(window.lucide){ lucide.createIcons(); }
}

window.addEventListener("load", ()=>{

  const savedTheme = localStorage.getItem("theme");

  if(savedTheme === "dark"){
    document.body.classList.add("dark");
  }

});

document.querySelectorAll(".dropdown p").forEach(item => {

  item.addEventListener("click", () => {

    const dropdown = document.getElementById("dropdown");
    const burger = document.getElementById("hamburger");

    dropdown.classList.remove("show");
    burger.classList.remove("active");

    burger.innerHTML = '<i data-lucide="menu" class="lucide-ic"></i>';
    if(window.lucide){ lucide.createIcons(); }

  });

});

const els = document.querySelectorAll('.fade');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('show');
  });
}, { threshold: 0.15 });

els.forEach(el => observer.observe(el));
