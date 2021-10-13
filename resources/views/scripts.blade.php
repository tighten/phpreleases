<script>
    window.onload = () => {
        const today = new Date();
        const date = {
            'day' : today.toLocaleString('default', { day: 'numeric' }),
            'month' : today.toLocaleString('default', { month: 'short' }),
            'year' : today.toLocaleString('default', { year: 'numeric'})
        };
        document.querySelectorAll('g.today > text')[0].innerHTML = `Today: ${date.day} ${date.month} ${date.year}`;
    }

</script>
