document.addEventListener("DOMContentLoaded", function () {
  // Navbar scroll effect
  window.addEventListener("scroll", function () {
    const navbar = document.querySelector(".navbar");
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }
  });

  // Cart functionality
  initCartFunctionality();

  // Product image gallery
  initProductGallery();

  // Form validations
  initFormValidations();
});

/**
 * Initialize cart functionality
 */
function initCartFunctionality() {
  // Update quantity in cart
  const quantityInputs = document.querySelectorAll(".quantity-input");
  if (quantityInputs) {
    quantityInputs.forEach((input) => {
      input.addEventListener("change", function () {
        const itemId = this.getAttribute("data-id");
        const newQuantity = parseInt(this.value);

        if (newQuantity <= 0) {
          this.value = 1;
          return;
        }

        // Send AJAX request to update cart
        updateCartItem(itemId, newQuantity);
      });
    });
  }

  // Remove items from cart
  const removeButtons = document.querySelectorAll(".remove-btn");
  if (removeButtons) {
    removeButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const itemId = this.getAttribute("data-id");

        // Send AJAX request to remove item
        removeCartItem(itemId);
      });
    });
  }

  // Add to cart buttons
  const addToCartButtons = document.querySelectorAll(".add-to-cart");
  if (addToCartButtons) {
    addToCartButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();

        const productId = this.getAttribute("data-id");
        const productSize =
          document.querySelector('select[name="size"]')?.value || "M";
        const quantity =
          document.querySelector('input[name="quantity"]')?.value || 1;

        // Send AJAX request to add item to cart
        addToCart(productId, productSize, quantity);
      });
    });
  }
}

/**
 * Update cart item quantity
 */
function updateCartItem(itemId, quantity) {
  fetch("cart-update.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `item_id=${itemId}&quantity=${quantity}&action=update`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update cart total
        document.querySelector(".cart-total").textContent = data.total;
        document.querySelector(".cart-badge").textContent = data.count;
      }
    })
    .catch((error) => {
      console.error("Error updating cart:", error);
    });
}

/**
 * Remove item from cart
 */
function removeCartItem(itemId) {
  fetch("cart-update.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `item_id=${itemId}&action=remove`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Remove item from DOM
        const cartItem = document.querySelector(`[data-cart-item="${itemId}"]`);
        cartItem.remove();

        // Update cart total
        document.querySelector(".cart-total").textContent = data.total;
        document.querySelector(".cart-badge").textContent = data.count;

        // If cart is empty, show empty message
        if (data.count === 0) {
          document.querySelector(".cart-items").innerHTML =
            "<p>Your cart is empty.</p>";
          document.querySelector(".cart-summary").style.display = "none";
        }
      }
    })
    .catch((error) => {
      console.error("Error removing item:", error);
    });
}

/**
 * Add item to cart
 */
function addToCart(productId, size, quantity) {
  fetch("cart-update.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `product_id=${productId}&size=${size}&quantity=${quantity}&action=add`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Show success message
        const message = document.createElement("div");
        message.className = "alert alert-success";
        message.innerHTML =
          'Product added to cart! <a href="cart.php">View Cart</a>';

        // Insert message after product form
        const productForm = document.querySelector(".product-form");
        productForm.parentNode.insertBefore(message, productForm.nextSibling);

        // Update cart badge
        document.querySelector(".cart-badge").textContent = data.count;

        // Remove message after 3 seconds
        setTimeout(() => {
          message.remove();
        }, 3000);
      }
    })
    .catch((error) => {
      console.error("Error adding to cart:", error);
    });
}

/**
 * Initialize product gallery functionality
 */
function initProductGallery() {
  const productThumbnails = document.querySelectorAll(".product-thumbnail");
  if (productThumbnails) {
    productThumbnails.forEach((thumb) => {
      thumb.addEventListener("click", function () {
        const mainImage = document.querySelector(".product-main-image");
        mainImage.src = this.getAttribute("data-image");

        // Remove active class from all thumbnails
        productThumbnails.forEach((t) => t.classList.remove("active"));

        // Add active class to selected thumbnail
        this.classList.add("active");
      });
    });
  }
}

/**
 * Initialize form validations
 */
function initFormValidations() {
  const forms = document.querySelectorAll("form");
  if (forms) {
    forms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        const requiredFields = form.querySelectorAll("[required]");
        let isValid = true;

        requiredFields.forEach((field) => {
          if (!field.value.trim()) {
            isValid = false;

            // Add error class to parent element
            field.parentElement.classList.add("error");

            // Create error message if it doesn't exist
            let errorMessage =
              field.parentElement.querySelector(".error-message");
            if (!errorMessage) {
              errorMessage = document.createElement("div");
              errorMessage.className = "error-message";
              errorMessage.textContent = "This field is required";
              field.parentElement.appendChild(errorMessage);
            }
          } else {
            // Remove error class and message
            field.parentElement.classList.remove("error");
            const errorMessage =
              field.parentElement.querySelector(".error-message");
            if (errorMessage) {
              errorMessage.remove();
            }
          }
        });

        if (!isValid) {
          e.preventDefault();
        }
      });
    });
  }
}
