<!--begin::Scrolltop-->
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true" aria-label="Scroll to top" role="button">
    <!--begin::Svg Icon | path: icons/duotone/Navigation/Up-2.svg-->
    <span class="svg-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 5l-6 6h4v7h4v-7h4l-6-6z" fill="currentColor" />
        </svg>
    </span>
    <!--end::Svg Icon-->
</div>
<!--end::Scrolltop-->

<style>
    #kt_scrolltop {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        background: #ffffff;
        border: 1px solid #dee2e6;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    #kt_scrolltop.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    #kt_scrolltop .svg-icon svg {
        width: 20px;
        height: 20px;
    }

    #kt_scrolltop .svg-icon svg * {
        fill: #111111 !important;
    }

    #kt_scrolltop:hover {
        transform: translateY(-2px);
        transition: transform 0.15s ease-in-out;
    }
</style>

<script>
    (function() {
        const scrollTopBtn = document.getElementById('kt_scrolltop');
        if (!scrollTopBtn) return;

        const toggleScrollTop = () => {
            if (window.scrollY > 200) {
                scrollTopBtn.classList.add('show');
            } else {
                scrollTopBtn.classList.remove('show');
            }
        };

        window.addEventListener('scroll', toggleScrollTop, { passive: true });
        toggleScrollTop();

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
</script>