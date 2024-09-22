    <?php 
        include_once "partials/head.php";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Formdan gelen verileri al
            $email = isset($_POST["email"]) ? $_POST["email"] : "";
            $password = isset($_POST["password"]) ? $_POST["password"] : "";
            $rememberMe = isset($_POST["rememberMe"]);

            // Kullanıcıyı giriş fonksiyonuyla kontrol et
            loginUser($email, $password, $rememberMe);
        }
    ?>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-2">Login</h3>
                                    </div>
                                    <div class="card-body">

                                        <?php
                                            // Hata mesajlarını göster
                                            if (isset($_GET["error"])) {
                                                showErrorMessage($_GET["error"]);
                                            }
                                        ?>
                                        
                                        <form action="login" method="post">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="email" type="email" name="email" placeholder="Email" required>
                                                <label for="email">Email</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="password" type="password" name="password" placeholder="Password" required>
                                                <label for="password">Password</label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" id="rememberMe" type="checkbox" name="rememberMe">
                                                <label class="form-check-label" for="rememberMe">Remember Me</label>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="password">Forgot Password?</a>
                                                <button class="btn btn-primary" type="submit">Login</button>
                                            </div>
                                        </form>

                                    </div>

                                    <div class="card-footer p-4">
                                        <a href="register" class="text-primary">
                                           <i class="fa fa-user-plus mr-2"></i>  Don't have an account? Sign up for free.
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
