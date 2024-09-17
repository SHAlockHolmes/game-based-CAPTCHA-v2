<?php
session_start();

$errorMessage = '';

if (isset($_GET['error'])) {
    // Get the error message
    $errorMessage = $_GET['error'];
}

$label = ["Shirt", "Ball", "Shoe"];

$max = 3;
$i = $j = $r = $flag = 0;
$randnum = array_fill(0, $max, 0);

for ($i = 0; $i < $max; $i++) {
    if ($i == 0) {
        $randnum[0] = mt_rand(1, $max);
    }
    do {
        $r = mt_rand(1, $max);
        $leave = 1;
        for ($j = 0; $j < $i; $j++) {
            if ($r == $randnum[$j])
                $flag++;
        }
        if ($flag == 0) {
            $randnum[$i] = $r;
            $leave = 0;
        }
        $flag = 0;
    } while ($leave != 0);
}

$key = implode("", $randnum);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Drag and Drop Game</title>
  <link rel="stylesheet" href="style_combined.css">
</head>

<body>


<div class="ult-container">
  <div class="info-container">
    <div class="text-heading">
      Drag and Drop CAPTCHA
    </div>
    <div class="text-subheading">
      Created by SPRITZ Research Group
    </div>
    <div class="text-info">
      Drag the correct object to it's corresponding labelled buckets to solve the CAPTCHA.
    </div>
  </div>
  <div class="ult-container-2">

    <div class="button-container">
        <div class="success-message" id="successMessage"></div>
        <div class="error-message" id="errorMessage"><?php echo htmlspecialchars(urldecode($errorMessage)); ?></div>
    </div>

      <div class="game-container">

        <!-- this section of code should be altered for n objects that come in from the backened ig -->

        <div class="object-container" id="oc">
          <img class="object" src="shirt.png" id="object1">
          <img class="object" src="ball.png" id="object2">
          <img class="object" src="shoe.png" id="object3">
        </div>

        <div class="area-container1">
          <div class="area1" id="bucket1"></div>
          <div class="text" id="bucket1text"><?php echo $label[$randnum[0] - 1]; ?> bucket</div>
        </div>

        <div class="area-container2">
          <div class="area2" id="bucket2"></div>
          <div class="text" id="bucket2text"><?php echo $label[$randnum[1] - 1]; ?> bucket</div>
        </div>

        <div class="area-container3">
          <div class="area3" id="bucket3"></div>
          <div class="text" id="bucket3text"><?php echo $label[$randnum[2] - 1]; ?> bucket</div>
        </div>

        <div class="logo-container" id="logo">
          <img src="logo.png">
        </div>
      </div>

    <div class="button-container">
      <!-- Reset button -->
      <input type="button" value="Reset" onclick="location.reload(true)">
      <!-- Submit button -->
      <input type="button" onclick="checkPlacement()" value="Submit">
    </div>
  </div>
</div>


<script>

    function scatterObjects() {
        const container = document.getElementById('oc');
        const obj = container.getElementsByClassName('object');
        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;

        for (let i = 0; i < obj.length; i++) {
            const ob = obj[i];
            const maxX = containerWidth - ob.offsetWidth;
            const maxY = containerHeight - ob.offsetHeight;

            const randomX = Math.floor(Math.random() * maxX);
            const randomY = Math.floor(Math.random() * maxY);

            ob.style.left = `${randomX}px`;
            ob.style.top = `${randomY}px`;
        }
    }

    // Call the function to scatter objects when the page loads
    window.onload = scatterObjects;



  const objects = document.querySelectorAll('.object');
  const buckets = document.querySelectorAll('.area1, .area2, .area3');

  var objectIdOrder = <?php echo json_encode($randnum); ?>;

  document.addEventListener("DOMContentLoaded", function() {
    const objects = document.querySelectorAll('.object');
    objects.forEach(function(object, index) {
      const objectId = object.id;
      const bucketId = 'bucket' + objectIdOrder[index];
      const bucket = document.getElementById(bucketId);
      if (bucket) {
        bucket.setAttribute('data-target', objectId);
      }
    });
  });

  objects.forEach(object => {
    object.addEventListener('dragstart', dragStart);
  });

  buckets.forEach(bucket => {
    bucket.addEventListener('dragover', dragOver);
    bucket.addEventListener('dragenter', dragEnter);
    bucket.addEventListener('dragleave', dragLeave);
    bucket.addEventListener('drop', drop);
  });

  function dragStart(event) {
    event.dataTransfer.setData('text/plain', event.target.id);
  }

  function dragOver(event) {
    event.preventDefault();
  }

  function dragEnter(event) {
    event.preventDefault();
    const currentBucket = event.target;
    currentBucket.classList.add('highlight');
  }

  function dragLeave(event) {
    const currentBucket = event.target;
    currentBucket.classList.remove('highlight');
  }

  function drop(event) {
    event.preventDefault();
    const droppedObjectID = event.dataTransfer.getData('text/plain');
    const droppedObject = document.getElementById(droppedObjectID);
    const currentBucket = event.target;
    currentBucket.classList.remove('highlight');
    if (currentBucket.classList.contains('area1') || currentBucket.classList.contains('area2') || currentBucket.classList.contains('area3')) {
        droppedObject.style.position = "static";
        droppedObject.style.left = null;
        droppedObject.style.top = null;
        currentBucket.appendChild(droppedObject);
    }
  }

  function checkPlacement() {
    let allCorrect = true;

    buckets.forEach(function(bucket) {
      const dataTarget = bucket.getAttribute('data-target');
      const objectIdInBucket = document.getElementById(dataTarget);
      if (!objectIdInBucket || objectIdInBucket.parentNode !== bucket) {
        allCorrect = false;
      }
    });

    const container = document.querySelector(".game-container");
    const successMessage = document.getElementById("successMessage");
    const errorMessage = document.getElementById("errorMessage");


    if (allCorrect) {
        // Change the container color to a custom green hex color
        container.style.backgroundColor = "#E9F5DB"; // Hex for green
        // Show success message
        successMessage.textContent = "CAPTCHA solved correctly";
        successMessage.className = "success-message";

        //In case there is an error message present in the container
        errorMessage.textContent = "";
        errorMessage.className = "error-message";

    } else {
        // Change the container color to a custom red hex color
        container.style.backgroundColor = "#E4B1AB"; // Hex for red
        errorMessage.textContent = "CAPTCHA solved incorrectly";
        errorMessage.className = "error-message";

        const errorMessageSend = encodeURIComponent("CAPTCHA solved incorrectly");
        // Refresh the page with the error message as a query parameter after a short delay to see the color change
        setTimeout(() => {
            window.location.href = window.location.href.split('?')[0] + '?error=' + errorMessageSend;
        }, 700);
      }
  }

</script>
</body>
</html>
