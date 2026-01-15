<?php
$config = require 'config.php';
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OpenCamp</title>
    <link rel="stylesheet" href="style.css">
    <!-- Include these in the <head> section of your PHP file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <!-- jQuery (required by Owl Carousel) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Owl Carousel JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <!--navbar-->
    <img id="bg-img" src="https://cdn.pixabay.com/photo/2025/02/22/08/35/mountain-9423779_1280.jpg" alt="">
    <main class="main">
      <h1><?php echo $config['site_name'];?></h1>
      <button class="btn btn-success w-50"><?php echo $config['button_text'] ?></button>
    </main>
    <nav class="navbar navbar-expand-lg  fixed-top " >
        <div class="container">
          <a class="navbar-brand" href="#"><?php echo $config['Nav-brand'];?></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse " id="navbarText">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">About us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Internship.php">Internships</a>
              </li>
              <li class="nav-item">
                <!-- <a class="nav-link" href="web_development.php">Courses</a> -->
                 <a href="services.php" class="nav-link">Services</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#Why_Choose_us">Why choose us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">Contact us</a>
              </li>
              <!-- <li class="nav-item">
                <a class="nav-link" href="blog.php">Blog</a>
              </li>
              <li class="nav-item dropdown" id="dropdown-1">
                <a class="nav-link dropdown-toggle" data-bs-target="#dropdown-1" data-bs-toggle="dropdown" href="#">Register</a>
                <ul class="dropdown-menu">
                  <li class="dropdown-item"><a href="#" data-bs-target="#register" data-bs-toggle="modal"  class="btn">Admin</a></li>
                  <li class="dropdown-item"><a href="#" data-bs-target="#register" data-bs-toggle="modal" class="btn">Trainee</a></li>
                </ul>
              </li>
              <li class="nav-item dropdown" id="dropdown-2">
                <a class="nav-link dropdown-toggle" data-bs-target="#dropdown-2" data-bs-toggle="dropdown" href="#">Login</a>
                <ul class="dropdown-menu">
                  <li class="dropdown-item"><a href="#" data-bs-target="#login" data-bs-toggle="modal" class="btn">Admin</a></li>
                  <li class="dropdown-item"><a href="#" data-bs-target="#login" data-bs-toggle="modal" class="btn">Trainee</a></li>
                </ul>
              </li>
            </ul> -->
            <span class="navbar-text">
             <a class="px-2" href=""><i class="fa-brands fa-facebook"></i></a>
             <a class="px-2" href=""><i class="fa-brands fa-twitter"></i></a>
             <a class="px-2" href=""><i class="fa-brands fa-whatsapp"></i></a>
             <a class="px-2" href=""><i class="fa-brands fa-instagram"></i></a>
            </span>
          </div>
        </div>
    </nav>
      
    <!--navbar-->
    <!--content-->
    <div class=" block container-1 container">
      <div class="row">
        <div class=" col col-xs col-md col-lg col-xl col-xxl">
          <div class="card-group">
            <div class="card ">
              <div class="card-body">
                <div class="card-img">
                  <img src="" alt="">
                </div>
                <h3 class="card-heading"><?php echo $config['card_heading_1'];?></h3>
                <p class="text-secondary"><?php echo $config['card_paragraph_1'];?></p>
              </div>
            </div>
            <div class="card card-2">
              <div class="card-body">
                <div class="card-img">
                  <img src="" alt="">
                </div>
                <h3 class="card-heading"><?php echo $config['card_heading_2'];?></h3>
                <p class="text-secondary"><?php echo $config['card_paragraph_2'];?></p>
              </div>
            </div>
            <div class="card ">
              <div class="card-body">
                <div class="card-img">
                  <img src="" alt="">
                </div>
                <h3 class="card-heading"><?php echo $config['card_heading_3'];?></h3>
                <p class="text-secondary"><?php echo $config['card_paragraph_3'];?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class=" block container-fluid container-2">
      <div class="content-2">
        <h1><?php echo $config['three_heading'];?></h1>
        <p><?php echo $config['three_paragraph'];?></p>
      </div>
    </div>
    <div class="container-fluid autoshow" >
        <div class="products-header">
            <h1 id="heading1"><?php echo $config['Our_Services'];?></h1>
        </div>
        <div class="container">
            <div class="slider">
                <div class="item">
                    <img src="./Assets/Aaqib_Rashid.jpeg" alt="">
                   <div class="item-content1">
                    <h3><?php echo $config['Role']; ?></h3>
                    <p><?php echo $config['Role1'];?></p>
                   </div>
                   <div class="item-content2">
                    <h3><?php echo $config['Specialization']; ?></h3>
                    <p><?php echo $config['Specialization1']; ?></p>
                   </div>
                </div>
                <div class="item">
                    <img src="./Assets/Jaffar_Hamid.jpeg" alt="">
                    <div class="item-content1">
                        <h3><?php echo $config['Role']; ?></h3>
                        <p><?php echo $config['Role2']; ?></p>
                    </div>
                    <div class="item-content2">
                        <h3><?php echo $config['Specialization']; ?></h3>
                        <p><?php echo $config['Specialization2']; ?></p>
                       </div>
                </div>
                <div class="item">
                    <img src="./Assets/Refat_Aara.jpeg" alt="">
                    <div class="item-content1">
                    <h3><?php echo $config['Role']; ?></h3>
                    <p><?php echo $config['Role3']; ?></p>
                   </div>
                   <div class="item-content2">
                    <h3><?php echo $config['Specialization']; ?></h3>
                    <p><?php echo $config['Specialization3']; ?></p>
                   </div>
                </div>
                <div class="item">
                    <img src="./Assets/uma.jpg" alt="">
                    <div class="item-content1">
                    <h3><?php echo $config['Role']; ?></h3>
                    <p><?php echo $config['Role4']; ?></p>
                   </div>
                   <div class="item-content2">
                    <h3><?php echo $config['Specialization']; ?></h3>
                    <p><?php echo $config['Specialization4']; ?></p>
                   </div>
                </div>
                <button id="next">></button>
                <button id="prev"><</button>
            </div>
            <!-- <img src="" alt="Leaf" id="img5"> -->
        </div>
    </div>
   <script>
    let items = document.querySelectorAll('.slider .item');
        let next = document.getElementById('next');
        let prev = document.getElementById('prev');
        let active =3;
        function loadShow(){
            let stt = 0;
    items.forEach(item => {
        const content1 = item.querySelector('.item-content-one, .item-content1');
        const content2 = item.querySelector('.item-content-two, .item-content2');
        if (content1) content1.className = 'item-content1';
        if (content2) content2.className = 'item-content2';
    });
    items[active].style.transform = `none`;
    items[active].style.zIndex = 1;
    items[active].style.filter = 'none';
    items[active].style.opacity = 1;
    const activeItem = items[active];
    const content1 = activeItem.querySelector('.item-content1');
    const content2 = activeItem.querySelector('.item-content2');
    if (content1) content1.className = 'item-content-one';
    if (content2) content2.className = 'item-content-two';
    for (var i = active + 1; i < items.length; i++) {
        stt++;
        items[i].style.transform = `translateX(${150 * stt}px) scale(${1 - 0.2 * stt}) perspective(16px) rotateY(-1deg)`;
        items[i].style.zIndex = -stt;
        items[i].style.filter = 'blur(5px)';
        items[i].style.opacity = stt > 2 ? 0 : 0.6;
    }

    stt = 0;
    for (var i = active - 1; i >= 0; i--) {
        stt++;
        items[i].style.transform = `translateX(${-150 * stt}px) scale(${1 - 0.2 * stt}) perspective(16px) rotateY(1deg)`;
        items[i].style.zIndex = -stt;
        items[i].style.filter = 'blur(5px)';
        items[i].style.opacity = stt > 2 ? 0 : 0.6;
    }
        }
        loadShow();
        setInterval(() => {
    active = (active + 1) % items.length;
    loadShow();
}, 3000);
        next.onclick=function(){
            active = active+1<items.length?active+1:active;
            loadShow();
        }
        prev.onclick=function(){
            active = active -1 >=0?active-1:active;
            loadShow();
        }
        items.forEach(item => {
        item.style.cursor = 'pointer'; // Optional: visually indicate it's clickable
        item.addEventListener('click', () => {
            window.location.href = 'Services.php';
        });
    });
   </script>

    <div class="block container container-4" id="Why_Choose_us">
      <div class="row">
        <div class="col-xs col-md col-lg col-xl col-xxl"> 
          <h1><?php echo $config['Why_us'];?></h1>
          <div>
            <h5><i class="bi bi-lightbulb-fill text-warning"></i> <?php echo $config['Why_us_heading_1'];?></h5>
            <p><?php echo $config['Why_us_paragraph_1'];?></p>
          </div>
          <hr>
          <div>
            <h5><i class="bi bi-lightbulb-fill text-warning"></i> <?php echo $config['Why_us_heading_2'];?></h5>
            <p><?php echo $config['Why_us_paragraph_2'];?></p>
          </div>
          <hr>
          <div>
            <h5><i class="bi bi-lightbulb-fill text-warning"></i> <?php echo $config['Why_us_heading_3'];?></h5>
            <p><?php echo $config['Why_us_paragraph_3'];?></p>
          </div>
          <hr>
        </div>
        <div class="col-xs col-md col-lg col-xl col-xxl">
          <h1><?php echo $config['Testimonials'];?></h1>
          <div class="carousel slide" id="carousel-1" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="<?php echo $config['location-1'];?>" alt="" class="d-block w-100">
              </div>
              <div class="carousel-item">
                <img src="<?php echo $config['location-2'];?>" alt="" class="d-block w-100">
              </div>
              <div class="carousel-item">
                <img src="<?php echo $config['location-3'];?>" alt="" class="d-block w-100">
              </div>
              <div class="carousel-item">
                <img src="<?php echo $config['location-4'];?>" alt="" class="d-block w-100">
              </div>
            </div>
            <button class="carousel-control-prev" data-bs-target="#carousel-1">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" data-bs-target="#carousel-1">
              <span class="carousel-control-next-icon"></span>
            </button>
          </div>
        </div>
      </div>
    </div>


    <!--content-->
    <!-- footer -->
    <div class="block container-fluid my-5">
      <footer class="text-center text-lg-start text-white" style="background-color: black">
        <section class="d-flex justify-content-between p-4" style="background-color: green" >
          <div class="me-5">
            <span><?php echo $config['join']; ?></span>
          </div>
          <div class="footer-social">
            <a href="<?php echo $config['social_facebook'];?>" class="text-white me-4">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="<?php echo $config['social_twitter'];?>" class="text-white me-4">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="<?php echo $config['social_google'];?>" class="text-white me-4">
              <i class="fab fa-google"></i>
            </a>
            <a href="<?php echo $config['social_instagram'];?>" class="text-white me-4">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="<?php echo $config['social_linkedin'];?>" class="text-white me-4">
              <i class="fab fa-linkedin"></i>
            </a>
          </div>
        </section>
        <section class="">
          <div class="container text-center text-md-start mt-5">
            <div class="row mt-3">
              <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold"><?php echo $config['Nav-brand'];?></h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto" />
                <p>
                <?php echo $config['footer_content'];?>
                </p>
              </div>
              <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold"><?php echo $config['courses'];?></h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto"/>
                <p>
                  <a href="#!" class="text-white"><?php echo $config['course_heading_1'];?></a>
                </p>
                <p>
                  <a href="#!" class="text-white"><?php echo $config['course_heading_2'];?></a>
                </p>
                <p>
                  <a href="#!" class="text-white"><?php echo $config['course_heading_3'];?></a>
                </p>
                <p>
                  <a href="#!" class="text-white"><?php echo $config['course_heading_4'];?></a>
                </p>
              </div>
              <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold"><?php echo $config['links'];?></h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto" />
                <p>
                  <a href="#!" class="text-white">Home</a>
                </p>
                <p>
                  <a href="#!" class="text-white">About us</a>
                </p>
                <p>
                  <a href="#!" class="text-white">Contact us</a>
                </p>
                <p>
                  <a href="#!" class="text-white">Help</a>
                </p>
              </div>
              <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                <h6 class="text-uppercase fw-bold">Contact</h6>
                <hr class="mb-4 mt-0 d-inline-block mx-auto" />
                <p><i class="fas fa-home mr-3"></i><?php echo $config['Address'];?></p>
                <p><i class="fas fa-envelope mr-3"></i> <?php echo $config['email'];?></p>
                <p><i class="fas fa-phone mr-3"></i><?php echo $config['contact_no'];?></p>
              </div>
            </div>
          </div>
        </section>
        <div class="text-center p-3"  style="background-color: rgba(0, 0, 0, 0.2)" >
          <a class="text-white" href="#">&copy;<?php echo $config['rights'];?></a>
        </div>
      </footer>
      <!-- Footer -->
    
    </div>
    <!-- End of .container -->
     <!-- footer -->
      <!--modal-->
      <div class="modal-wrapper">
        <div class="modal fade" id="register">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-body">
              <div class="row">
                <div class="col-12">
                  <button class=" float-end btn-close" data-bs-dismiss="modal"></button>
                  <h3 class="display-6"><?php echo $config['register']; ?></h3>  
                </div>
                <div class="col-12 form-one">
                  <form action="" method="POST">
                    <div >
                      <input type="hidden" name="register-id" id="register-id">
                      <div class="row">
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <input type="text" name="fname" id="fname" class="form-control" placeholder="Firstname" required>
                        </div>
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <input type="text" name="lname" id="lname" class="form-control" placeholder="Lastname" required>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <input type="number" name="mobile" id="mobile" class="form-control" placeholder="Mobile" required>
                          <div class="mobile_val"></div>
                        </div>
                        <div class="col col-xs col-md col-lg col-xl col-xxl" >
                          <select name="type" id="type" class="form-select" required>
                            <option value="">Type of account</option>
                            <option value="ADMIN">Admin</option>
                            <option value="TRAINEE">Trainee</option>
                          </select>
                          </ul>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <input type="email" id="email_address" name="email_address" class="form-control" placeholder="username@domain.domain-name">
                        </div>
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <input type="password" name="reg_password" id="reg_password" class="form-control" placeholder="Password">
                          <div class="strengthmsg"></div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <button class="btn btn-primary d-block mx-auto my-2 w-25 btn-submit" type="submit" name="register">Register</button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--modal-->
      <div class="modal-wrapper">
        <div class="modal fade" id="login">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-body">
              <div class="row">
                <div class="col-12">
                  <button class=" float-end btn-close" data-bs-dismiss="modal"></button>
                  <h3 class="display-6"><?php echo $config['login']; ?></h3>  
                </div>
                <div class="col-12 form-one">
                  <form action="" method="POST">
                    <div >
                      <div class="row">
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <input type="email" id="login_email" name="login_email" class="form-control" placeholder="username@domain.domain-name">
                        </div>
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <input type="password" name="login_password" id="login_password" class="form-control" placeholder="Password">
                        </div>
                      </div>
                      <div class="row">
                        <div class="col col-xs col-md col-lg col-xl col-xxl">
                          <button class="btn btn-primary d-block mx-auto my-2 w-25 btn-submit" type="submit" name="login">Login</button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <script src="main.js"></script>
    <script>
       document.getElementById("password").addEventListener("input", function() {
            let password = this.value;
            let strengthMsg = document.querySelector(".strengthmsg");
            let hasUpperCase = /[A-Z]/.test(password);
            let hasLowerCase = /[a-z]/.test(password);
            let hasNumber = /[0-9]/.test(password);
            let isLongEnough = password.length >= 8;

            if (!isLongEnough) {
                strengthMsg.textContent = "Length must be at least 8";
                strengthMsg.className = "strengthmsg length-error"; 
            } else if (hasUpperCase && hasLowerCase && hasNumber) {
                strengthMsg.textContent = "Strong Password";
                strengthMsg.className = "strengthmsg strong";
            } else {
                strengthMsg.textContent = "Weak Password";
                strengthMsg.className = "strengthmsg weak";
            }
        });
    </script>
    <script>
        document.getElementById("mobile").addEventListener("input", function() {
            let mobile = this.value;
            let mobileMsg = document.querySelector(".mobile_val");

            // Regular expression to check 10-digit number starting with 6,7,8,9
            let mobilePattern = /^[6789]\d{9}$/;

            if (mobilePattern.test(mobile)) {
                mobileMsg.textContent = "Valid number";
                mobileMsg.className = "mobile_val valid"; // Green color
            } else {
                mobileMsg.textContent = "Enter valid number";
                mobileMsg.className = "mobile_val invalid"; // Red color
            }
        });
        function Pay(){
          alert("Login or Register now too access the course");
        }
    </script>
    <script src="main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html> 