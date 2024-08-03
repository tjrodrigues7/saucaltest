(function( $ ) {
	'use strict';

const baseUrl = `${window.location.protocol}//${window.location.host}`;
const omdbSearchEndpoint = '/wp-json/omdb/v1/omdb-search';
const omdbSearchUrl = baseUrl + omdbSearchEndpoint;

let headers = {};
if (typeof wpApiSettings !== 'undefined') {
    headers['X-WP-Nonce'] = wpApiSettings.nonce;
}

/**
 * Search meadia on OMDB using RestAPI
 */
$(document).on('submit', '#omdb-search-form', function(e) {
	e.preventDefault(); 

	$("#omdb-search-btn").prop('disabled', true).toggle();
	$("#omdb-search-form .ajax-loader").toggle();

	const formData = new FormData(e.target);
    const data = {};
    formData.forEach((value, key) => { data[key] = value; });

	fetch(omdbSearchUrl, {
		method: 'POST',
		body: formData,
		credentials: 'include',
		headers: headers
	})
	.then(response => response.json())
	.then(data => {
		if (data.status === 200) {
			const fields = [
                { label: 'Title', key: 'Title' },
                { label: 'Year', key: 'Year' },
                { label: 'Rated', key: 'Rated' },
                { label: 'Released', key: 'Released' },
                { label: 'Runtime', key: 'Runtime' },
                { label: 'Genre', key: 'Genre' },
                { label: 'Director', key: 'Director' },
                { label: 'Writer', key: 'Writer' },
                { label: 'Actors', key: 'Actors' },
                { label: 'Plot', key: 'Plot' },
                { label: 'Language', key: 'Language' },
                { label: 'Country', key: 'Country' },
                { label: 'Awards', key: 'Awards' },
                { label: 'IMDB Rating', key: 'imdbRating' },
                { label: 'Type', key: 'Type' },
                { label: 'Box Office', key: 'BoxOffice' },
            ];
			
			let resultsHtml = `<p><strong>Source:</strong> ${data.cached ? 'Cached Results' : 'API Results'}</p>`;
            resultsHtml += `<img class="omdb-poster" src="${data.results.Poster}" alt="${data.results.Title} Poster" />`;

            fields.forEach(field => {
                if (data.results[field.key]) {
                    resultsHtml += `
                        <span><strong>${field.label}:</strong> ${data.results[field.key]}</span>
                    `;
                }
            });

            $("#omdb-results").html(resultsHtml);
			
		} else {

			$("#omdb-results").html("No results found");
			
		}
		$("#omdb-search-form .ajax-loader").toggle();
		$("#omdb-search-btn").prop('disabled', false).toggle();
	})
	.catch((error) => {
		console.error('Error:', error);
		$("#omdb-search-btn").prop('disabled', false).toggle();
		$("#omdb-search-form .ajax-loader").toggle();
	});
});

})( jQuery );
