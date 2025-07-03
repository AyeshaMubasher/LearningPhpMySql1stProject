<!DOCTYPE html>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="validation.js"></script>
  <title>Test Project</title>
  <style>
    table {
      border-collapse: collapse;
      width: 60%;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #aaa;
      padding: 10px;
      text-align: left;
    }
    th {
      background-color: #f2f2f2;
    }
    .error {
      color: red;
      font-size: 0.9em;
    }
    .mobile-error {
      color: red;
      font-size: 0.9em;
    }
  </style>
</head>
<body>

<h1>Test Project</h1>

<form id="userForm" action="save.php" method="POST" enctype="multipart/form-data">
  <div>
    <label>Name</label>
    <input type="text" name="name" id="name" />
    <span class="error" id="nameError"></span>
  </div>
  <br>

  <div>
    <label>Email</label>
    <input type="email" name="email" id="email" />
    <span class="error" id="emailError"></span>
  </div>
  <br>

  <div>
    <label>Upload Image:</label>
    <input type="file" name="user_image" accept="image/*" required id="image" />
    <span class="error" id="imageError"></span>
  </div>
  <br>

  <div id="mobileNumbersContainer">
    <label>Mobile Numbers:</label>
    <div class="mobile-field">
      <input type="text" name="mobile_numbers[]" placeholder="Enter mobile number" />
      <span class="mobile-error"></span>
    </div>
  </div>
  <button type="button" id="addMobileBtn">+ Add Mobile Number</button>

  <br><br>
  <div>
    <input type="submit" value="Save" name="save" />
  </div>
</form>

<h2>All Users</h2>
<?php include 'display.php'; ?>

</body>
</html>
