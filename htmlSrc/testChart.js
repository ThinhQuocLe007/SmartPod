const ctx = document.getElementById('temperatureChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: [1, 2, 3],
        datasets: [{ label: 'Test Data', data: [10, 20, 30], borderColor: 'blue' }]
    }
});
