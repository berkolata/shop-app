    <?php 
        include_once "partials/head.php";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Formdan gelen verileri kontrol et
            $name = isset($_POST["name"]) ? $_POST["name"] : "";
            $surname = isset($_POST["surname"]) ? $_POST["surname"] : "";
            $email = isset($_POST["email"]) ? $_POST["email"] : "";
            $password = isset($_POST["password"]) ? $_POST["password"] : "";
            $passwordConfirm = isset($_POST["passwordConfirm"]) ? $_POST["passwordConfirm"] : "";
        
            // Form işlemlerini gerçekleştiren fonksiyonu çağır
            registerUser($name, $surname, $email, $password, $passwordConfirm);
        }
    ?>

    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-7">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-2">Create Account</h3>
                                    </div>

                                    <div class="card-body">
                                        <?php
                                            // Hata veya başarı mesajını göster
                                            displayRegistrationMessages();
                                        ?>
                                        <form id="registrationForm" action="register.php" method="post">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <input class="form-control" id="name" name="name" type="text" placeholder="Enter your first name" require />
                                                        <label for="name">First name</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input class="form-control" id="surname" name="surname" type="text" placeholder="Enter your last name" require />
                                                        <label for="surname">Last name</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="email" name="email" type="email" placeholder="name@imahealthgroup.com" require />
                                                <label for="email">Email address</label>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <span class="text-danger">Minimum 8 characters</span>
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <input class="form-control" id="password" name="password" type="password" placeholder="Create a password" require />
                                                        <label for="password">Password</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-danger">Minimum 8 characters</span>
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <input class="form-control" id="passwordConfirm" name="passwordConfirm" type="password" placeholder="Confirm password" require />
                                                        <label for="passwordConfirm">Confirm Password</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4 mb-0">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        Create Account
                                                    </button>
                                                </div>
                                            </div>
                                        </form>

                                        <script>
                                            // JavaScript ile formu gizle
                                            $(document).ready(function () {
                                                <?php if (isset($_GET["success"]) && $_GET["success"] == "registration_successful"): ?>
                                                    // Kayıt başarılıysa formu gizle
                                                    $('#registrationForm').hide();
                                                <?php endif; ?>
                                            });
                                        </script>
                                    </div>

                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="login">Have an account? Go to login</a></div>
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
