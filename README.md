# yelp-bizcomp
Business competition visualizer based on Yelp data.

## Introduction
This is a simple php based web application that starts with a business's yelp business id, and then shows competing businesses. To compare apples to apples, I'm loading only the businesses with the same category list.

## Working prototype
http://gautamtandon.me/yelp-bizcomp

NOTE: Please be mindful of the Yelp API usage thresholds. Thanks!

## Setup

### Pre-requisites
1. PHP based web server (such as Apache).

### Assumptions
1. Document root of your web server is "/var/www/html"
2. Your server can be accessed at http://localhost

### Steps
1. Download yelp-bizcomp into your /var/www/html folder.
2. Edit yelpclient.php with your Yelp keys.

## Usage
1. Find your business Id on yelp, or pick any business's yelp id.
2. Hit this URL in your browser: http://localhost?id=[yelp-business-id]

### Tips
1. You can drag the map to a different location to load new set of competitors in that view area.
2. Double clicking on any business would reload the map with that business in the center and will then load its competitors.
3. Right clicking on any business will load its yelp page in a separate window/tab.

## FAQ

### How can I find business Id of any business on yelp?
Simply search for that business on yelp.com and go to it's page. Look at the browser's URL. It will be something like "https://www.yelp.com/biz/[yelp-business-id]". That's the yelp business Id.

### What do those color codes mean?
Colors correspond to Yelp rating.
Red: 1
Orange: 2
Yellow: 3
Lime: 4
Green: 5
In order to find the corresponding color, I am rounding off the yelp rating to the nearest integer. For example, 3.6 will be treated as 4 and 3.2 will be treated as 3.

### What's that star on some circles?
That means that the business is offering deals or gift certificates.
