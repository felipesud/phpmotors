<?php
//custom functions

function checkEmail($clientEmail){
    $valEmail = filter_var($clientEmail, FILTER_VALIDATE_EMAIL);
    return $valEmail;
}

function checkPassword($clientPassword){
    $pattern = '/^(?=.*[[:digit:]])(?=.*[[:punct:]\s])(?=.*[A-Z])(?=.*[a-z])(?:.{8,})$/';
    return preg_match($pattern, $clientPassword);
}

function checkClasslist($classificationId){
    if($classificationId ==! 0){
        $classificationId === "";
    }
    return $classificationId;
}

function navList($classifications){
    $navList = '<ul>';
    $navList .= "<li><a href='/phpmotors/index.php'>Home</a></li>";
    foreach ($classifications as $classification){
        $navList .="<li><a href='/phpmotors/vehicles/?action=classification&classificationName=".urlencode($classification['classificationName'])."' title='View our $classification[classificationName] lineup of vehicles'>$classification[classificationName]</a></li>";
    }
    $navList .='</ul>';
    return $navList;
}

function buildClassificationList($classLists){
    $classificationList = '<select name="classificationId" id="classificationList">';
    $classificationList .= "<option>Choose  a Classification</option>";
    foreach ($classLists as $classification){
        $classificationList .= "<option value = '$classification[classificationId]'>$classification[classificationName]</option>";
    }
    $classificationList .= '</select>';
    return $classificationList;
}

function getInventoryByClassification($classificationId){
    $db = phpmotorsConnect();
    $sql = 'SELECT * FROM inventory WHERE classificationId = :classificationId';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':classificationId', $classificationId, PDO::PARAM_INT);
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $inventory;
}

// display of vehicles
function buildVehiclesDisplay($vehicles){
    $dv = '<ul id="inv-display">';
    foreach ($vehicles as $vehicle){
        $dv .= '<li>';
        $dv .= "<a href='/phpmotors/vehicles/?action=vehicle-detail&vehicleId=$vehicle[invId]'><img src='$vehicle[imgPath]' alt='Image of $vehicle[invMake] $vehicle[invModel] on phpmotors.com'>";
    $dv .= '<hr>';
    $dv .= "<h2>$vehicle[invMake] $vehicle[invModel]</h2>";
    $dv .= "<span>$". number_format($vehicle['invPrice']) ."</span></a>";
    $dv .= '</li>';
    }
    $dv .= '</ul>';
    return $dv;
}

function buildVehicleSelectedDisplay($vehicleSelected, $thumbnails){
    
    foreach($vehicleSelected as $vehicleData){
        $dvs = "<h1>$vehicleData[invMake] $vehicleData[invModel]</h1>";
        $dvs .= "<section>";
        $dvs .=  "<aside>";
        foreach($thumbnails as $thumbnail){
            $dvs .= "<img class='thumbnail' src='$thumbnail[imgPath]' alt='Image of $vehicleData[invMake] $vehicleData[invModel] on phpmotors.com'></img>";
        }
        $dvs .=  "</aside>";
        $dvs .= "<img id='principal' src='$vehicleData[imgPath]' alt='Image of $vehicleData[invMake] $vehicleData[invModel] on phpmotors.com'></img>";
        $dvs .=  "<article>";
        $dvs .= "<p class='price'>Price: $". number_format($vehicleData['invPrice']) ."<span></span></p>";
        $dvs .= "<p class='description'>$vehicleData[invDescription]</p>";
        $dvs .= "<p class='color'>Color: <span>$vehicleData[invColor]</span></p>";
     //   $dvs .= "<p class='stock'># In Stock: <span>$vehicleData[invStock]</span></p>";
        $dvs .=  "</article>";
    }
    $dvs .= "</section>";
    return $dvs;

}

/* * ********************************
*  Functions for working with images
* ********************************* */

// Adds "-tn" designation to file name
function makeThumbnailName($image) {
    $i = strrpos($image, '.');
    $image_name = substr($image, 0, $i);
    $ext = substr($image, $i);
    $image = $image_name . '-tn' . $ext;
    return $image;
}

// Build images display for image management view
function buildImageDisplay($imageArray) {
    $id = '<ul id="image-display">';
    foreach ($imageArray as $image) {
        $id .= '<li>';
        $id .= "<img src='$image[imgPath]' title='$image[invMake] $image[invModel] image on PHP Motors.com' alt='$image[invMake] $image[invModel] image on PHP Motors.com'>";
        $id .= "<p><a href='/phpmotors/uploads?action=delete&imgId=$image[imgId]&filename=$image[imgName]' title='Delete the image'>Delete $image[imgName]</a></p>";
        $id .= '</li>';
    }
    $id .= '</ul>';
    return $id;
}

// Build the vehicles select list
function buildVehiclesSelect($vehicles) {
    $prodList = '<select name="invId" id="invId">';
    $prodList .= "<option>Choose a Vehicle</option>";
    foreach ($vehicles as $vehicle) {
     $prodList .= "<option value='$vehicle[invId]'>$vehicle[invMake] $vehicle[invModel]</option>";
    }
    $prodList .= '</select>';
    return $prodList;
}

// Handles the file upload process and returns the path
// The file path is stored into the database
function uploadFile($name) {
    // Gets the paths, full and local directory
    global $image_dir, $image_dir_path;
    if (isset($_FILES[$name])) {
        // Gets the actual file name
        $filename = $_FILES[$name]['name'];
        if (empty($filename)) {
            return;
        }
        // Get the file from the temp folder on the server
        $source = $_FILES[$name]['tmp_name'];
        // Sets the new path - images folder in this directory
        $target = $image_dir_path . '/' . $filename;
        // Moves the file to the target folder
        move_uploaded_file($source, $target);
        // Send file for further processing
        processImage($image_dir_path, $filename);
        // Sets the path for the image for Database storage
        $filepath = $image_dir . '/' . $filename;
        // Returns the path where the file is stored
        return $filepath;
    }
}

// Processes images by getting paths and 
// creating smaller versions of the image
function processImage($dir, $filename) {
    // Set up the variables
    $dir = $dir . '/';
   
    // Set up the image path
    $image_path = $dir . $filename;
   
    // Set up the thumbnail image path
    $image_path_tn = $dir.makeThumbnailName($filename);
   
    // Create a thumbnail image that's a maximum of 200 pixels square
    resizeImage($image_path, $image_path_tn, 200, 200);
   
    // Resize original to a maximum of 500 pixels square
    resizeImage($image_path, $image_path, 500, 500);
}

// Checks and Resizes image
function resizeImage($old_image_path, $new_image_path, $max_width, $max_height) {
     
    // Get image type
    $image_info = getimagesize($old_image_path);
    $image_type = $image_info[2];
   
    // Set up the function names
    switch ($image_type) {
    case IMAGETYPE_JPEG:
        $image_from_file = 'imagecreatefromjpeg';
        $image_to_file = 'imagejpeg';
    break;
    case IMAGETYPE_GIF:
        $image_from_file = 'imagecreatefromgif';
        $image_to_file = 'imagegif';
    break;
    case IMAGETYPE_PNG:
        $image_from_file = 'imagecreatefrompng';
        $image_to_file = 'imagepng';
    break;
    default:
        return;
   } // ends the swith
   
    // Get the old image and its height and width
    $old_image = $image_from_file($old_image_path);
    $old_width = imagesx($old_image);
    $old_height = imagesy($old_image);
   
    // Calculate height and width ratios
    $width_ratio = $old_width / $max_width;
    $height_ratio = $old_height / $max_height;
   
    // If image is larger than specified ratio, create the new image
    if ($width_ratio > 1 || $height_ratio > 1) {
   
     // Calculate height and width for the new image
     $ratio = max($width_ratio, $height_ratio);
     $new_height = round($old_height / $ratio);
     $new_width = round($old_width / $ratio);
   
     // Create the new image
     $new_image = imagecreatetruecolor($new_width, $new_height);
   
     // Set transparency according to image type
     if ($image_type == IMAGETYPE_GIF) {
        $alpha = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
        imagecolortransparent($new_image, $alpha);
     }
   
     if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
     }
   
     // Copy old image to new image - this resizes the image
     $new_x = 0;
     $new_y = 0;
     $old_x = 0;
     $old_y = 0;
     imagecopyresampled($new_image, $old_image, $new_x, $new_y, $old_x, $old_y, $new_width, $new_height, $old_width, $old_height);
   
     // Write the new image to a new file
     $image_to_file($new_image, $new_image_path);
     // Free any memory associated with the new image
     imagedestroy($new_image);
     } else {
     // Write the old image to a new file
     $image_to_file($old_image, $new_image_path);
     }
     // Free any memory associated with the old image
     imagedestroy($old_image);
} // ends resizeImage function

// Buind the search results
function buildSearchResults($sResults){
    $searchDisplay = '<section name="sResult" id="sResult">';
    foreach ($sResults as $sResult){
        $searchDisplay .= "<article>";
        $searchDisplay .= "<a href='/phpmotors/vehicles/?action=vehicle-detail&vehicleId=$sResult[invId]'><h2>$sResult[invYear] $sResult[invMake] $sResult[invModel]</h2></a>";
        $searchDisplay .= "<img src='$sResult[imgPath]' title='$sResult[invMake] $sResult[invModel] image on PHP Motors.com' alt='$sResult[invMake] $sResult[invModel] image on PHP Motors.com'>";
        $searchDisplay .= "<p>$sResult[invDescription]</p>";
        $searchDisplay .= "</article>";
    }
    $searchDisplay .= '</section>';
    return $searchDisplay;
}

// Build pagination bar
function pagination($totalPages, $page, $searchBar)
{
  // THIS CREATES THE PAGINATION LINKS AT THE BOTTOM OF THE SEARCH WHICH ALLOWS US TO GO FROM PAGE TO PAGE.
    $previous = $page - 1;
    $next = $page + 1;

    //1 page
    if($totalPages <= 1){
        $paginationBar = "";
    }
    //1+ result
    else {
        // Div with numbers
        $paginationBar = '<article id="pagination">';

        //if current page is > 1, show the previous link
        if ($page > 1){
            $paginationBar .= '<a style="color:blue" title="Previous page" href="/phpmotors/search/index.php?action=search&page=' . $previous . '&searchBar= ' . $searchBar . '"><< Previous</a>';
        }

        //Create page numbers link
        for ($page_num = 1; $page_num <= $totalPages; $page_num++){
            if ($page_num != $page) {
                $paginationBar .= '<a style="color:blue" title="Page ' . $page_num . '" href="/phpmotors/search/index.php?action=search&page=' . $page_num . '&searchBar=' . $searchBar . '">' . $page_num . '</a>';
            } else {
                $paginationBar .= '<a style="pointer-events: none;color:black" href="/phpmotors/search/index.php?action=search&page=' . $page_num . '">' . $page_num . '</a>';
            }
        };

        if ($page < $totalPages){
            $paginationBar .= '<a style="color:blue" title="Next page" href="/phpmotors/search/index.php?action=search&page=' . $next . '&searchBar= ' . $searchBar . '">Next >></a>';
        }
        $paginationBar .= '</article>';
    }
    return $paginationBar;
}

//Build reviews display in vehicle details view
function buildReviewsDisplay($reviews) {
    $review = '<div id="review-display">';
      foreach ($reviews as $r) {
        $first = $r['clientFirstname'][0];
        $last = $r['clientLastname'];
        $time = strtotime($r['reviewDate']);
        $dateFormatted = (date("d F, Y",$time));
  
        $review .= '<div>';
        $review .= "<h4>$first$last wrote on $dateFormatted:</h4>";
        $review .= "<p>$r[reviewText]</p>";
        $review .= '</div>';
       
      }
      $review .= '</div>';
      return $review;
  }
  
function buildclientreviews($clientReviews) {
    $cr = '<div id="client-reviews">';
    $cr .= '<ul>';
      foreach ($clientReviews as $r) {
        $time = strtotime($r['reviewDate']);
        $dateFormatted = (date("d F, Y",$time));
        $id = $r['reviewId'];
        $vehicleId = $r['invId'];
        $clientId = $_SESSION['clientData']['clientId'];
        
        $cr .= "<li>$r[invMake] $r[invModel] (Reviewed on $dateFormatted): 
                <a href='/phpmotors/reviews/?action=edit-review&reviewId=$id&clientId=$clientId&invId=$vehicleId'>Edit</a> 
                | <a href='/phpmotors/reviews/?action=delete-review&reviewId=$id&clientId=$clientId&invId=$vehicleId'>Delete</a>";
        $cr .= "<ul><li class='listSpace'>$r[reviewText]</li></ul>";
        $cr .= '</li>';
      }
      $cr .= '</ul>';
      $cr .= '</div>';
      return $cr;
  }

 

//build a display of all the vehicle details within an unordered list   
function buildDetailsDisplay($vehicle){
    $details = '<div class="vehicleDetails">';
    $details .= '<div id="child-1">';
    $details .= "<img src='$vehicle[imgPath]' alt='Image of $vehicle[invMake] $vehicle[invModel] on phpmotors.com'>";
    $currency = number_format($vehicle['invPrice']);
    $details .= "<h2>$$currency</h2>";
    $details .= '</div>';
    $details .= '<div id="child-2">';
    $details .= "<ul>";
    $details .= "<li>$vehicle[invMake] $vehicle[invModel] Details</li>";
    $details .= "<li>$vehicle[invDescription]</li>";
    $details .= "<li>Color: $vehicle[invColor]</li>"; 
    $details .= "<li># in Stock: $vehicle[invStock]</li>";
    $details .= "</ul>";
    $details .= '</div>';
    $details .= '</div>';
    return $details;
   }

?>