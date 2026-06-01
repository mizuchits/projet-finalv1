// import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.scss';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

const menu = document.querySelector('.menu');
const nav = document.querySelector('.links-menu');

menu.addEventListener('click', () => {
  nav.classList.toggle('nav-menu');
});

document.addEventListener('DOMContentLoaded', function() {
    
    let mybutton = document.getElementById("myBtn");

    window.onscroll = function() {
        scrollFunction();
    };

    function scrollFunction() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    function topFunction() {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    }

    mybutton.addEventListener('click', topFunction);
});