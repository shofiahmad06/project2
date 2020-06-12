
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
            <div class="col-12 menu-height menu-1">
            
              <nav>
                <ul class="main-ul">
                    
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="product.php">PRODUCRT</a></li>
                    <li><a href="Top_brands.php">TOP_BRANDS</a></li>
                    <li><a href="cart.php">CART</a></li>
                    <li><a href="contact.php">CONTACT</a></li>
                     <li><a href="login.php">Log In</a></li>
                    <li><a href="signUp.php">SingUp</a></li>

                </ul>
                
                
              </nav>
              <div class="search_box">
                    <form>
                        <input type="text" value="Search for Products" style="height: 47px;" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Search for Products';}"><input type="submit" value="SEARCH" style="height: 47px;">
                    </form>
                </div>
            </div>
            <div class="row"></div>
            <div class="col-12 big-footer-height test-1 signUp" id="up">
                  
                 <div class="loginbox2">
                    <i class="fa fa-close" id="cross" style="font-size:36px;color:red;position:absolute;top: 0px;right: 0px;"></i>
                    <img src="contact.png" class="avatar2">
                     <h1>SignUp here</h1>
                     <form>
                         <p>First Name</p>
                         <input type="text" name="" placeholder="Enter First Name" require>
                         <p>Second Name</p>
                         <input type="text" name="" placeholder="Enter Second Name" require>
                         <p>Email</p>
                         <input type="text" name="email" placeholder="Enter Email" require>
                         <p>Mobile No</p>
                         <input type="text" name="value" placeholder="Enter Mobile No">
                         <p>Password</p>
                         <input type="password" name="" placeholder="Enter Password" required>
                         <p>Confirm Password</p>
                         <input type="password" name="" placeholder="repeat Password" required>
                         <input type="submit" name="" value="SignUp">
                         <a href="s">Lost your password?</a><br>
                         <a href="s">Don't have an account</a>
                     </form>
                 </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 header-height">
               
                <div class="col-3 big-footer-height test-6 sigTest">
                        <h4>&nbsp &nbsp &nbsp Information </h4>
                        <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Customer Service</a></li>
                        <li><a href="#"><span>Advanced Search</span></a></li>
                        <li><a href="#">Orders and Returns</a></li>
                        <li><a href="#"><span>Contact Us</span></a></li>
                        </ul>
                    </div>


                    <div class="col-3 big-footer-height test-6 sigTest">
                    <h4>&nbsp &nbsp &nbsp Why buy from us</h4>
                        <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="faq.php">Customer Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="contact.php"><span>Site Map</span></a></li>
                        <li><a href="preview.php"><span>Search Terms</span></a></li>
                        </ul>
                </div>

                <div class="col-3 big-footer-height test-6 sigTest">
                    <h4>&nbsp &nbsp &nbsp Contact</h4>
                        <ul>
                            <li><span>+88-01758770333</span></li>
                            <li><span>+88-01300817784</span></li>
                        </ul>
                        <div class="fa">
                            <h4>&copy;Follow Us</h4>
                              <ul>
                                  <li class="fa-facebook"><a href="#" target="_blank"> </a></li>
                                  <li class="fa-twitter"><a href="#" target="_blank"> </a></li>
                                  <li class="fa-google"><a href="#" target="_blank"> </a></li>
                                  <li class="contact"><a href="#" target="_blank"> </a></li>
                                  <div class="clear"></div>
                             </ul>
                        </div>
                </div>
            </div>
            </div>
        </div>

    </div>


</div>
<script>
 var crs=document.getElementById('cross');
 var log=document.getElementById('up');
 window.onclick=function(event){
    if (event.target==crs) {
        log.style.display="none";
    }
 }
</script>
</body>
</html>