document.getElementById('download-btn').addEventListener('click', function () {
    const element = document.getElementById('certificate');

    const options = {
        margin:       0.2, // 0.2 inch margins
        filename:     'EcoRise_Certificate.pdf', // Name of the downloaded file
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true }, // scale: 2 makes the text/images sharper
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' } 
    };

    html2pdf().set(options).from(element).save();
});