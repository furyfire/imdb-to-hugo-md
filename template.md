---
title: "{{ title|e('yaml')  }} ({{ movie.year() }})"
summary: "{{ movie.plotoutline()|e('yaml') }}"
date: {{rating['Date Rated']}}

links:
  - icon_pack: fab
    icon: imdb
    name: IMDb
    url: '{{movie.main_url()}}'
{% for trailer in movie.trailers(TRUE) %}
  - icon_pack: fas
    icon: film
    name: "{{trailer['title']|e('yaml') }}"
    url: '{{trailer['url']}}'
{% endfor %}
tags:
{% for genre in movie.genres() %}
  - "{{genre|e('yaml')}}"
{% endfor %}
---
![Poster][logo]

> _"{{movie.tagline()}}"_



# Rating
I rated the movie {{ rating['Your Rating']}}/10.

IMDb has an average rating of {{movie.rating()}}/10 from {{movie.votes()}} votes.

# Plot
{{ movie.plotoutline() }}

[logo]: {{ movie.photo(false) }} "{{ title|e('yaml')  }}"