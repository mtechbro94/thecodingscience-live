<?php
$config = require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile Card</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .profile-section {
  display: flex;
  min-height: 100vh;
}

    .left-side {
  background-color: #2e2e2e;
  width: 400px;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1;
}

    .right-side {
  margin-left: 400px; /* Equal to .left-side width */
  padding: 40px;
  background-color: white;
  flex: 1;
  overflow-y: auto;
  min-height: 100vh;
}

    .profile-img {
      max-width: 100%;
      border: 4px solid #fff;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .profile-info h1 {
        margin-top:30px;
      font-weight: 900;
      margin-bottom: 0;
    }
    .profile-info h5{
        display:inline !important;
    }
    .profile-info h4 {
      font-weight: 300;
      color: #555;
    }

    @media (max-width: 768px) {
      .profile-section {
        flex-direction: column;
      }

      .right-side {
        padding: 20px;
        justify-content: center;
        text-align: center;
      }
    }
     .education-section {
      margin-top: 50px;
    }

    .education-title {
      font-weight: 700;
    }

    .degree-title {
      color: #5a5a5a;
      font-weight: 500;
      font-size: 1.2rem;
    }

    .education-duration {
      font-weight: 600;
      margin-top: 5px;
    }

    .education-status {
      font-style: italic;
      color: #555;
      margin-bottom: 10px;
    }

    .education-description {
      font-size: 1rem;
      color: #333;
      max-width: 700px;
    }
    .col-5{
        padding:30px !important;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
        margin:30px;
        transition: 0.3s ease;
    }
    .col-5:hover{
         transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);  
    }
    .btn {
        margin-top:30px;
        width: 250px;
  font-size:16px;
  font-weight: 600;
  background-color:#154633;
  padding:24px 24px 24px 32px;
  display:flex;
  align-items:center;
  border-radius:99px;
  position:relative;
  transition: all .5s cubic-bezier(.77,0,.175,1); 
  .text{
    color:#fff;
    line-height:1;
    position:relative;
    z-index:5;
    margin-right:92px;
  }
  
  svg{
    display:inline-block;
    position:relative;
    z-index:5;
    transform: rotate(0deg) translateX(0);
    transform-origin: left;
    transition: all .5s cubic-bezier(.77,0,.175,1);
  }
  
  &::before{
    content:'';
    background-color:#95C11F;
    width:35px;
    height:35px;
    display:block;
    position:absolute;
    z-index:1;
    border-radius:99px;
    top:50%;
    right:20px;
    transform:translateY(-50%);
    transition: all .5s cubic-bezier(.77,0,.175,1);
  }
  
  &.light{
    background-color: #95C11F;
    
    &::before{
      background-color: #154633;
    }
    
  }
}

.btn:hover{
  
  svg{
    transform: rotate(45deg) translateX(-8px);
  }
  
  &::before{
    content:'';
    width:100%;
    height:100%;
    right:0;
  }
}
  </style>
</head>
<body>

<div class="profile-section">
  <div class="left-side">
    <img src="./Assets/jaffar_hamid.jpeg" alt="Olivia Sanchez" class="profile-img" width="300" height="300">
  </div>
  <div class="right-side">
    <div class="profile-info">
      <h1><?php echo $config['Trainer-name-2'];?><sub>
      <h5 class="text-secondary "><?php echo $config['Trainer-Role-2'];?></h5></sub></h1>
      <p class="my-3">"<?php echo $config['Trainer-desc-2'];?>"</p>
      <div class="container education-section">
  <h3 class="education-title"><?php echo $config['education-title-2'];?></h3>
  <div class="mt-4">
    <div class="degree-title"><?php echo $config['degree-title-2'];?></div>
</div>
</div>
<div class="container experience-section mt-4">
  <h3 class="education-title"><?php echo $config['Specialization-heading-2'];?></h3>

  <div class="mt-4">
    <div class="position-title"><?php echo $config['position-title-2'];?></div>
  </div>

</div>
<div class="container experience-section mt-4">
  <h3 class="education-title"><?php echo $config['Foundations-2'];?></h3>

  <div class="mt-4">
    <div class="degree-title"><?php echo $config['trainer-spec-2'];?></div>
    <ul>
        <li><?php echo $config['trainer-spec-point-6'];?></li>
        <li><?php echo $config['trainer-spec-point-7'];?></li>
        <li><?php echo $config['trainer-spec-point-8'];?></li>
        <li><?php echo $config['trainer-spec-point-9'];?></li>
        <li><?php echo $config['trainer-spec-point-10'];?></li>
    </ul>
</div>
    </div>
    <div class="mt-4">
    <div class="degree-title"><?php echo $config['curriculum-highlight-1'];?></div>
    <ul>
       <li><?php echo $config['currcuum-point-10'];?></li>
       <li><?php echo $config['currcuum-point-11'];?></li>
       <li><?php echo $config['currcuum-point-12'];?></li>
       <li><?php echo $config['currcuum-point-13'];?></li>
       <li><?php echo $config['currcuum-point-14'];?></li>
       <li><?php echo $config['currcuum-point-15'];?></li>
       <li><?php echo $config['currcuum-point-16'];?></li>
       <li><?php echo $config['currcuum-point-17'];?></li>
       <li><?php echo $config['currcuum-point-18'];?></li>
    </ul>
</div>
    </div>
   <a class="btn light" href="#">
      <span class="text"><?php echo $config['enroll-btn'];?></span>
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" ><path d="M4.66669 11.3334L11.3334 4.66669" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.66669 4.66669H11.3334V11.3334" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
