<?php
$config = require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .profile-card {
      background: linear-gradient(to bottom, #2db5f9 50%, #ffffff 50%);
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 20px;
       cursor: pointer;
      text-align: center;
      transition: 0.3s ease;
    }
    .profile-card:hover {
    transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  }
    .profile-img {
      border-radius: 10px;
      width: 200px !important;
      height: 200px;
      object-fit: contain;
      max-height: 350px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      margin-bottom: 15px;
    }

    .profile-name {
      font-weight: 700;
      font-size: 1.4rem;
      color: #333;
    }

    .profile-role {
      color: #1a8fe3;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .icon-circle {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: white;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin: 0 5px;
    }
    h1{
      font-size: 3rem;
    font-family: 'Georgia', serif;
    color: #2d2d2d;
    position: relative;
    }

    
    </style>
    <script src="https://kit.fontawesome.com/66aa7c98b3.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">    
</head>
<body>
   <div class="container mt-2">
    <h1 class=""><?php echo $config['trainer-title']; ?></h1>
   </div>
    <div class="container py-5">
     
  <div class="row g-4">
    <div class="col-md-3" id="computer">
       <!-- <h2>Computer fundamentals</h2> -->
      <div class="profile-card">
        <img src="./Assets/Refat_Aara.jpeg" alt="Emilia Roy" class="profile-img">
        <div class="profile-name"><?php echo $config['profile-name-1']; ?></div>
        <div class="profile-role"><?php echo $config['profile-role-1']; ?></div>
    </div>
  </div>
    <div class="col-md-3" id="programming">
       <!-- <h2>Programming</h2> -->
      <div class="profile-card">
        <img src="./Assets/Jaffar_hamid.jpeg" alt="Emilia Roy" class="profile-img ">
        <div class="profile-name"><?php echo $config['profile-name-2']; ?> </div>
        <div class="profile-role"><?php echo $config['profile-role-2']; ?></div>
        
    </div>
  </div>
  <div class="col-md-3" id="web">
       <!-- <h2>Web development</h2> -->
      <div class="profile-card">
        <img src="./Assets/uma.jpg" alt="Emilia Roy" class="profile-img">
        <div class="profile-name"><?php echo $config['profile-name-3']; ?></div>
        <div class="profile-role"><?php echo $config['profile-role-3']; ?></div>
        
    </div>
  </div>
  <div class="col-md-3" id="datascience">
       <!-- <h2>Data Science And AI</h2> -->
      <div class="profile-card">
        <img src="./Assets/Aaqib_Rashid.jpeg" alt="Emilia Roy" class="profile-img">
        <div class="profile-name"><?php echo $config['profile-name-4']; ?></div>
        <div class="profile-role"><?php echo $config['profile-role-4']; ?></div>
        
    </div>
</div>
  </div>
  </div>
  <script>
  document.getElementById("computer").addEventListener("click", function() {
    window.location.href = "computer.php";
  });

  document.getElementById("programming").addEventListener("click", function() {
    window.location.href = "programming.php";
  });

  document.getElementById("web").addEventListener("click", function() {
    window.location.href = "web.php";
  });

  document.getElementById("datascience").addEventListener("click", function() {
    window.location.href = "datascience.php";
  });

  document.getElementById("Artificial").addEventListener("click", function() {
    window.location.href = "artificial.php";
  });
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>