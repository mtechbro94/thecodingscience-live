document.addEventListener('scroll',()=>{
    const nav = document.querySelector('nav');
    if(window.scrollY>0){
        nav.classList.add('navbar-scrolled');
    }else{
        nav.classList.remove('navbar-scrolled');
    }
})
