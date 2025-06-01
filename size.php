<?php
$pageTitle = "Size Guide";
require_once 'includes/header.php';
?>
<style>
    .container {
        min-width: 80vh;
        margin: 0 auto;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    h1 {
        color: #222;
        margin-bottom: 10px;
        font-size: 32px;
    }

    .subtitle {
        color: #666;
        font-size: 16px;
        margin-bottom: 5px;
    }

    .product-selector {
        display: flex;
        justify-content: center;
        margin: 25px 0;
        gap: 15px;
    }

    .product-btn {
        padding: 10px 25px;
        background-color: #f1f1f1;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        color: #555;
        transition: all 0.3s ease;
    }

    .product-btn.active {
        background-color: #357e3d;
        color: #fff;
    }

    .product-content {
        display: none;
    }

    .product-content.active {
        display: block;
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>
<div class="container">
    <header>
        <h1>Women's Size Guide</h1>
        <p class="subtitle">Find your perfect fit for our printed T-shirts and Hoodies</p>
    </header>

    <div class="product-selector">
        <button class="product-btn active" onclick="switchProduct('tshirts')">T-Shirts</button>
        <button class="product-btn" onclick="switchProduct('hoodies')">Hoodies</button>
    </div>

    <div id="tshirts" class="product-content active">
        <div class="size-chart">
            <h2 class="chart-title">Women's T-Shirt Size Chart</h2>
            <table class="size-table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Chest (inches)</th>
                        <th>Waist (inches)</th>
                        <th>Length (inches)</th>
                        <th>US Size</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>XS</td>
                        <td>31-33</td>
                        <td>24-26</td>
                        <td>25</td>
                        <td>0-2</td>
                    </tr>
                    <tr>
                        <td>S</td>
                        <td>33-35</td>
                        <td>26-28</td>
                        <td>26</td>
                        <td>4-6</td>
                    </tr>
                    <tr>
                        <td>M</td>
                        <td>35-37</td>
                        <td>28-30</td>
                        <td>27</td>
                        <td>8-10</td>
                    </tr>
                    <tr>
                        <td>L</td>
                        <td>38-40</td>
                        <td>31-33</td>
                        <td>28</td>
                        <td>12-14</td>
                    </tr>
                    <tr>
                        <td>XL</td>
                        <td>41-43</td>
                        <td>34-36</td>
                        <td>29</td>
                        <td>16-18</td>
                    </tr>
                    <tr>
                        <td>2XL</td>
                        <td>44-46</td>
                        <td>37-39</td>
                        <td>30</td>
                        <td>20-22</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="fit-info">
            <div class="fit-card">
                <h4>Regular Fit</h4>
                <p>Our standard t-shirts feature a comfortable regular fit that's neither too tight nor too loose. Perfect for everyday wear and suitable for most body types.</p>
            </div>
            <div class="fit-card">
                <h4>Relaxed Fit</h4>
                <p>Our relaxed fit t-shirts offer a looser, more casual style with extra room throughout the body and sleeves for maximum comfort.</p>
            </div>
        </div>
    </div>

    <div id="hoodies" class="product-content">
        <div class="size-chart">
            <h2 class="chart-title">Women's Hoodie Size Chart</h2>
            <table class="size-table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Chest (inches)</th>
                        <th>Waist (inches)</th>
                        <th>Length (inches)</th>
                        <th>US Size</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>XS</td>
                        <td>33-35</td>
                        <td>25-27</td>
                        <td>26</td>
                        <td>0-2</td>
                    </tr>
                    <tr>
                        <td>S</td>
                        <td>35-37</td>
                        <td>27-29</td>
                        <td>27</td>
                        <td>4-6</td>
                    </tr>
                    <tr>
                        <td>M</td>
                        <td>37-39</td>
                        <td>29-31</td>
                        <td>28</td>
                        <td>8-10</td>
                    </tr>
                    <tr>
                        <td>L</td>
                        <td>40-42</td>
                        <td>32-34</td>
                        <td>29</td>
                        <td>12-14</td>
                    </tr>
                    <tr>
                        <td>XL</td>
                        <td>43-45</td>
                        <td>35-37</td>
                        <td>30</td>
                        <td>16-18</td>
                    </tr>
                    <tr>
                        <td>2XL</td>
                        <td>46-48</td>
                        <td>38-40</td>
                        <td>31</td>
                        <td>20-22</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="fit-info">
            <div class="fit-card">
                <h4>Standard Hoodie</h4>
                <p>Our standard hoodies feature a classic fit with ribbed cuffs and hem. They provide enough room for comfort without being overly baggy.</p>
            </div>
            <div class="fit-card">
                <h4>Oversized Hoodie</h4>
                <p>Our oversized hoodies offer a trendy, roomier fit with extended shoulders and a longer cut for that fashionable, cozy style.</p>
            </div>
        </div>
    </div>

    <div class="how-to-measure">
        <h3>How to Measure Yourself</h3>
        <div class="measure-grid">
            <div class="measure-item">
                <div class="measure-icon">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"></circle>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                    </svg>
                </div>
                <div class="measure-text">
                    <h4>Chest / Bust</h4>
                    <p>Measure around the fullest part of your chest/bust, keeping the measuring tape horizontal.</p>
                </div>
            </div>

            <div class="measure-item">
                <div class="measure-icon">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"></path>
                        <path d="M6 12h12"></path>
                    </svg>
                </div>
                <div class="measure-text">
                    <h4>Waist</h4>
                    <p>Measure around your natural waistline, which is the narrowest part of your torso.</p>
                </div>
            </div>

            <div class="measure-item">
                <div class="measure-icon">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12h20"></path>
                        <path d="M12 2v20"></path>
                    </svg>
                </div>
                <div class="measure-text">
                    <h4>Length</h4>
                    <p>Measure from the highest point of your shoulder down to where you want the hem to sit.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="size-tips">
        <h3>Size & Fit Tips</h3>
        <ul>
            <li>Our t-shirts and hoodies are designed with standard US women's sizing.</li>
            <li>If you're between sizes, we recommend choosing the larger size for a more comfortable fit.</li>
            <li>For a looser, more relaxed fit, consider going up one size.</li>
            <li>Our printed designs do not affect the fit or stretch of the garments.</li>
            <li>All measurements have a tolerance of ±0.5 inches due to the nature of garment production.</li>
        </ul>
    </div>
</div>

<!-- Footer and newsletter are handled globally or in the layout -->

<script>
    function switchProduct(product) {
        // Hide all product content
        const contents = document.getElementsByClassName('product-content');
        for (let i = 0; i < contents.length; i++) {
            contents[i].classList.remove('active');
        }

        // Show selected product content
        document.getElementById(product).classList.add('active');

        // Update button states
        const buttons = document.getElementsByClassName('product-btn');
        for (let i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove('active');
        }

        // Find the clicked button and add active class
        const activeButtons = document.querySelectorAll(`.product-btn`);
        for (let i = 0; i < activeButtons.length; i++) {
            if (activeButtons[i].innerHTML.toLowerCase().includes(product.slice(0, -1))) {
                activeButtons[i].classList.add('active');
            }
        }
    }
</script>

</div>
<?php
// Include footer
include 'includes/footer.php';
?>