<style>
.mobile {
    display: none;
}
@media (max-width: 768px) {
    .mobile {
        display: block;
    }
}
.desktop {
    display: none;
}
@media (min-width: 992px) {
    .desktop {
        display: block;
    }
}

</style>

<img class="container-fluid img-fluid desktop" src="{$desktop}"/>
<img class="container-fluid img-fluid mobile" src="{$mobile}"/>