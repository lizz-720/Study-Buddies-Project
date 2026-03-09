const addButton = document.getElementById("addEvent");
const eventList = document.getElementById("eventList");

addButton.addEventListener("click", function () {

  const date = document.getElementById("eventDate").value;
  const text = document.getElementById("eventText").value;

  if (date === "" || text === "") {
    return;
  }

  const li = document.createElement("li");

  li.textContent = date + " - " + text;

  eventList.appendChild(li);

});