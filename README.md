# yelp-bizcomp
Business competition visualizer based on Yelp data.

## Introduction
This is a simple php based web application that starts with a business's yelp business id, and then shows competing businesses. To compare apples to apples, I'm loading only the businesses with the same category list.

## Setup
0. You'll need a php based web server to make this work. I'm assuming you have apache server and are installing this in the usual "/var/www/html" area.
1. Download yelp-bizcomp into your /var/www/html
2. Edit YelpClient.php with your consumer key, secret, token and token secret. You can find this information in your Yelp account.

## Usage
Once you have set it up, simply go to your server (say http://localhost/yelp-bizcomp). The browser will ask you to share your location and will start from that point, with a google marker of your location in the center. In the text box at the top, simply provide your business's yelp business id and click "Find Competition".
