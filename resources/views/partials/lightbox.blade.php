{{-- Image pop-up filled by site.js from the clicked [data-lightbox] group. --}}
<dialog class="lightbox" data-lightbox-dialog aria-label="{{ __('Xem ảnh') }}">
    <figure class="lightbox__figure">
        <img data-lightbox-image alt="">
        <figcaption class="lightbox__caption"><span data-lightbox-caption></span> <span class="lightbox__counter" data-lightbox-counter></span></figcaption>
    </figure>
    <button type="button" class="lightbox__btn lightbox__btn--prev" data-lightbox-prev aria-label="{{ __('Ảnh trước') }}"><x-ui-icon name="arrow-left" /></button>
    <button type="button" class="lightbox__btn lightbox__btn--next" data-lightbox-next aria-label="{{ __('Ảnh sau') }}"><x-ui-icon name="arrow-right" /></button>
    <button type="button" class="lightbox__btn lightbox__btn--close" data-lightbox-close aria-label="{{ __('Đóng') }}"><x-ui-icon name="close" /></button>
</dialog>
