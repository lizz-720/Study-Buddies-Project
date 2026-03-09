const features = [
  {
    title: "Flashcard",
    image: "images/flashcard.jpg",
    page: "flashcard.html"
  },
  {
    title: "Fill in the Blank",
    image: "images/fillblank.jpg",
    page: "fillblank.html"
  },
  {
    title: "Calendar",
    image: "images/calendar.jpg",
    page: "calendar.html"
  },
  {
    title: "Self Timer",
    image: "images/selftimer.jpg",
    page: "timer.html"
  }
];

let currentIndex = 0;

const image = document.getElementById("featureImage");
const title = document.getElementById("featureTitle");

const leftButton = document.querySelector(".left-button");
const rightButton = document.querySelector(".right-button");
const bottomButton = document.querySelector(".bottom-button");

function updateFeature() {
  image.src = features[currentIndex].image;
  title.textContent = features[currentIndex].title;
}

rightButton.addEventListener("click", function () {
  currentIndex++;

  if (currentIndex >= features.length) {
    currentIndex = 0;
  }

  updateFeature();
});

leftButton.addEventListener("click", function () {
  currentIndex--;

  if (currentIndex < 0) {
    currentIndex = features.length - 1;
  }

  updateFeature();
});

bottomButton.addEventListener("click", function () {
  window.location.href = features[currentIndex].page;
});