document.querySelector(".search i").onclick = function() {
    this.style.display = "none";
    this.parentElement.querySelector("input").style.display = "block";
    this.parentElement.querySelector("input").focus();
};
document.querySelector(".search input").onkeyup = function(event) {
    if (event.keyCode === 13 && this.value.length > 0) {
        if (rewrite_url) {
            window.location.href = encodeURI(base_url + "search/" + this.value);
        } else {
            window.location.href = encodeURI(base_url + "index.php?page=search&query=" + this.value);
        }
    }
};
if (document.querySelector(".product-img-small")) {
    let imgs = document.querySelectorAll(".product-img-small");
    imgs.forEach(function(img) {
        img.onclick = function() {
            document.querySelector(".product-img-large").src = this.src;
            imgs.forEach(i => i.classList.remove("selected"));
            this.classList.add("selected");
        };
    });
}
if (document.querySelector(".products-form")) {
    let products_form_submit = function() {
        document.querySelector(".products-form")
        if (rewrite_url) {
            window.location.href = encodeURI(base_url + "products/" + document.querySelector(".category select").value + "/" + document.querySelector(".sortby select").value);
        } else {
            window.location.href = encodeURI(base_url + "index.php?page=products&category=" + document.querySelector(".category select").value + "&sort=" + document.querySelector(".sortby select").value);
        }
    };
    document.querySelector(".sortby select").onchange = () => products_form_submit();
    document.querySelector(".category select").onchange = () => products_form_submit();
}
if (document.querySelector(".product #product-form")) {
    document.querySelectorAll(".product #product-form select").forEach(ele => {
        ele.onchange = () => {
            let price = 0.00;
            document.querySelectorAll(".product #product-form select").forEach(e => {
                if (e.value) {
                    price += parseFloat(e.options[e.selectedIndex].dataset.price);
                }
            });
            if (price > 0.00) {
                document.querySelector(".product .price").innerHTML = currency_code + price.toFixed(2);
            }
        };
    });
}
document.querySelector(".responsive-toggle").onclick = function(event) {
    event.preventDefault();
    let nav_display = document.querySelector("header nav").style.display;
    document.querySelector("header nav").style.display = nav_display == "block" ? "none" : "block";
};
if (document.querySelector(".cart .ajax-update")) {
    document.querySelectorAll(".cart .ajax-update").forEach(ele => {
        ele.onchange = () => {
            let formEle = document.querySelector(".cart form");
            let formData = new FormData(formEle);
            formData.append("update", "Update");
            fetch(formEle.action, {
                method: "POST",
                body: formData
            }).then(response => response.text()).then(function(html) {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, "text/html");
                document.querySelector(".subtotal").innerHTML = doc.querySelector(".subtotal").innerHTML;
                document.querySelector(".shipping").innerHTML = doc.querySelector(".shipping").innerHTML;
                document.querySelector(".discount").innerHTML = doc.querySelector(".discount").innerHTML;
                document.querySelector(".discount-code .result").innerHTML = doc.querySelector(".discount-code .result").innerHTML;
                document.querySelector(".total").innerHTML = doc.querySelector(".total").innerHTML;
                document.querySelectorAll(".product-total").forEach((e,i) => {
                    e.innerHTML = doc.querySelectorAll(".product-total")[i].innerHTML;
                })
            });
        };
    });
}
