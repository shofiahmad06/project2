

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DeviceStoreBd</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">



</head>
    <body>
     <div>
       

    <div class="wrapper">

        <div class="row" style="background-color: gray;">
            <div class="col-3 header-height">
             <img src="images/logo.png" height="200" width="100%">
            </div>
            <div class="col-9 header-height">
                <img src="images/banner.png" height="200" width="100%">
            </div> 
        </div>

        <div class="row">
            <div class="col-12 menu-height menu-1" id="navbar">
               
              <nav>
                <ul class="main-ul">
                    
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="product.php">PRODUCRT</a></li>
                    <li><a href="Top_brands.php">TOP_BRANDS</a></li>
                    <li><a href="cart.php">CART</a></li>
                    <li><a href="contact.php">CONTACT</a></li>
                     <li><a href="#" onclick="document.getElementById('pop').style.display='block'">Log In</a></li>
                    

                 
                </ul>
                
                 <div class="search_box">
                    <form>
                        <input type="text" value="Search for Products" style="height: 47px;" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Search for Products';}"><input type="submit" value="SEARCH" style="height: 47px;">
                    </form>
                </div>
              </nav>
              </div>
  
            </div>
        </div>
              

        <div class="col-12 big-footer-height test-1" id="pop" id="lo">
                  
                 <div class="loginbox">
                        <i class="fa fa-close" id="cross"style="font-size:36px;color:red;position:absolute;top: 0px;right: 0px;"></i>
                    <img src="contact.png" class="avatar">

                     <h1>Login here</h1>
                     <form action="product.html" method="post" >
                         <p>Username</p>
                         <input type="text" name="username" placeholder="Enter Username">
                         <p>Password</p>
                         <input type="password" name="password" placeholder="Enter Password">
                         <input type="submit" name="login" value="Login">
                         
                         <a href="#" onclick="document.getElementById('up').style.display='block'">Create New Account</a>
                     </form>
                 </div>
            </div>
            
              

          <div class="col-12 big-footer-height test-1 signUp" id="up">
                  
                 <div class="loginbox2">
                    <i class="fa fa-close" id="cros" style="font-size:36px;color:red;position:absolute;top: 0px;right: 0px;"></i>
                    <img src="contact.png" class="avatar2">
                     <h1>SignUp here</h1>
                     <form action="index.php" method="post">
                         <p>First Name</p>
                         <input type="text" name="firstname"  placeholder="Enter First Name" required>
                         <p>Last Name</p>
                         <input type="text" name="lastname" placeholder="Enter Second Name" required>
                         <p>Email</p>
                         <input type="text" name="email" placeholder="Enter Email" required>
                         <p>Mobile No</p>
                         <input type="text" name="num"  placeholder="Enter Mobile No" required>
                         <p>Password</p>
                         <input type="password" name="password" placeholder="Enter Password" required>
                         
                         <input type="submit" name="create" value="SignUp">
                        
                     </form>
                 </div>
            </div>
            

     </div>
        <div class="row">
            <div class="col-12 header">
              <div class="slides"><img src="images/1.jpg" style="width:100%; height: 300px;"></div>
              <div class="slides"><img src="images/2.jpg" style="width:100%; height: 300px;"></div>
              <div class="slides"><img src="images/3.jpg" style="width:100%; height: 300px;"></div>
              <div class="slides"><img src="images/4.jpg" style="width:100%; height: 300px;"></div>
              <div style="text-align:center">
              <span class="dot"></span> 
              <span class="dot"></span> 
               <span class="dot"></span> 
               <span class="dot"></span> 
              </div>
          </div>
        </div>
                 <div class="row">
                   <div class="col-12 menu-height"></div>
            
                 </div>

        <div class="row">
            
            <div class="col-3 big-footer-height test-1">
                <a href="preview.php"><img src="images/pic2.jpg" alt="" / ></a>
                <div class="text"><h3>NIKON</h3>
                <h4><b>Price:</b>2400TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>                          
            </div>
            <div class="col-3 big-footer-height test-1">
                
                          <a href="preview.php"><img src="images/pic3.jpg" alt="" / ></a>
                          
                          
              <div class="text"><h3>SAMSUNG</h3>
                <h4><b>Price:</b>2999TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>                          
            </div>
            
            <div class="col-3 big-footer-height test-1">
                <a href="preview.php"><img src="images/pic2.jpg" alt="" / ></a>
               <div class="text"><h3>NIKON</h3>
                <h4><b>Price:</b>3999TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>

            </div>
            <div class="col-3 big-footer-height test-1">
                <a href="preview.php"><img src="images/pic1.png" alt="" / ></a>
                <div class="text"><h3>CANON</h3>
                <h4><b>Price:</b>4999TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            
            
             
            
        </div>

            <div class="row">
                   <div class="col-12 menu-height"><h2>FEATURE PRODUCTS</h2></div>
            
           </div>
        <div class="row">
            <div class="col-3 big-footer-height test-2">
                <a href="preview.php"><img src="images/pic1.png" alt="" / ></a>
                <div class="text"><h3>CANON</h3>
                <h4><b>Price:</b>3999TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            
            <div class="col-3 big-footer-height test-2">
                <a href="preview.php"><img src="images/lap.png" alt="" / ></a>
                <div class="text"><h3>HP</h3>
                <h4><b>Price:</b>5200TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            <div class="col-3 big-footer-height test-2">
                <a href="preview.php"><img src="images/lap2.png" alt="" / ></a>
                <div class="text"><h3>DELL LAPTOP 2</h3>
                <h4><b>Price:</b>5000 TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            
            <div class="col-3 big-footer-height test-2">
                <a href="preview.php"><img src="images/laptop.png" alt="" / ></a>
                <div class="text"><h3>SAMSUNG MINI</h3>
                <h4><b>Price:</b>6999TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            
            
            
        </div>
        <div class="row">
                   <div class="col-12 menu-height"><h2>NEW PRODUCT</h2></div>
            
         </div>

        <div class="row">
            
           <div class="col-3 big-footer-height test-1">
                <a href="preview.php"><img src="images/pic4.png" alt="" / ></a>
               <div class="text"><h3>IPHONE</h3>
                <h4><b>Price:</b>1500 TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            <div class="col-3 big-footer-height test-1">
                <a href="preview.php"><img src="images/samsung.jpg" alt="" / ></a>
               <div class="text"><h3>Galaxy</h3>
                <h4><b>Price:</b>7990TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><a href="preview.php">Details</a></span></div>
            </div>
            <div class="col-3 big-footer-height test-1">
                <a href="preview.php"><img src="images/pre.jpg" alt="" / ></a>
               <div class="text"><h3>CC CAMERA</h3>
                <h4><b>Price:</b>1999 TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            <div class="col-3 big-footer-height test-1">
                <a href="preview.php"><img src="images/fe.jpg" alt="" / ></a>
               <div class="text"><h3>SOUND BOX</h3>
                <h4><b>Price:</b>1990 TK</h4>
                </div> 
                <div class="button"><span><a href="cart.php">Add to cart &nbsp;&nbsp;</a></span></div>
                 <div class="button1"><span><a href="preview.php">Details</a></span></div>
            </div>
            
           
             
            
        </div>

        <div class="row">
            <div class="col-12 header-height">
                <div class="col-3 big-footer-height test-6">
                        <h4> &nbsp &nbsp &nbsp Information </h4>
                        <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Customer Service</a></li>
                        <li><a href="#"><span>Advanced Search</span></a></li>
                        <li><a href="#">Orders and Returns</a></li>
                        <li><a href="#"><span>Contact Us</span></a></li>
                        </ul>
                    </div>


                    <div class="col-3 big-footer-height test-6">
                    <h4>&nbsp &nbsp &nbsp Why buy from us</h4>
                        <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="faq.php">Customer Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="contact.php"><span>Site Map</span></a></li>
                        <li><a href="preview.php"><span>Search Terms</span></a></li>
                        </ul>
                </div>

                <div class="col-3 big-footer-height test-6">
                    <h4>Contact</h4>
                        <ul>
                            <li><span>+88-01765744899</span></li>
                            <li><span>+88-01685366854</span></li>
                        </ul>
                        <div class="fa">
                            <h4>&copy;Follow Us</h4>
                              <ul>
                                  <a href="https://www.facebook.com" target="_blank" class="fa-facebook"> </a>
                                   <a href="www.twitter.com" target="_blank" class="fa-twitter"></a>
                                  <a href="www.google.com" target="_blank" class="fa-google"> </a>
                                  <li class="contact"><a href="#" target="_blank"> </a></li>
                                  <div class="clear"></div>
                             </ul>
                        </div>
                </div>
                    
                </div>
            </div>
        </div>

    </div>
    <!-- <h1 id="eror">system is error?</h1> -->
<script>
var slideIndex = 0;
showSlides();

function showSlides() {
  var i;
  var slides = document.getElementsByClassName("slides");
  var dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}    
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active";
  setTimeout(showSlides, 2000); 
  // showSlides();
}
// login
var crs=document.getElementById('cross');
 var log=document.getElementById('pop');
 //create new account
 var upp=document.getElementById('up').style.display='none';
 var cl=document.getElementById('up');
 var logg=document.getElementById('cros');
 window.onclick=function(event){
  if (event.target==crs) {
    log.style.display="none";
  }
  else if(event.target==upp){
    upp.style.display="block";
    
  }
  else if(event.target==logg){
    cl.style.display="none";
    log.style.display="none";
  }

 }

 // var cros=document.getElementById('sign');
 // document.getElementById('sign').style.display="block";
 // var logg=document.getElementById('up');
 // window.onclick=function(event){
 //    if (event.target==cros) {
 //        logg.style.display="none";
 //    }
 // }


 window.onscroll = function() {scrollFunction()};

function scrollFunction() {
  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    document.getElementById("navbar").style.top = "0";
     document.getElementById("navbar").style.position = 'fixed';
      document.getElementById("navbar").style.width = '100%';
  } else {
    document.getElementById("navbar").style.top = "0px";
    document.getElementById("navbar").style.position = 'relative';
  }
}
</script>


</body>
</html>

 