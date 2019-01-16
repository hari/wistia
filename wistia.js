const fetch = require('node-fetch');
const fs = require('fs');

const API_KEY = 'ca53f7568dff9c273f68bf4f70d0eee8a3a3be181992100188ad2d3288b07a79';
const API_URL = 'https://api.wistia.com/v1/medias.json?api_password=';

const urls = `
if you want some specific urls only, paste them here
`;

const createCSV = medias => {
  const result = [];
  for (const media of medias) {
    if (media.type === 'Video') {
      result.push([
        media.name,
        media.thumbnail.url,
        media.assets.find(asset => asset.type === 'OriginalFile').url + '.mp4'
      ].join(','));
    }
  }
  result.unshift(['Name', 'Thumbnail', 'Video'].join(','));
  return result.join("\r\n");
}

const saveCSV = data => fs.writeFile('./videos-' + Date.now() + '.csv', data, err => {
  if (err) {
    console.log('Error while saving: ' + err.message);
    return;
  }
  console.log('Saved!');
});

const getIds = () => {
  return urls.
    split(/\s/gi)
    .filter(url => url.trim().length && url.includes('/medias/'))
    .map(url => url.split('/').pop());
}

const getVideos = async () => {
  try {
    const response = await fetch(API_URL + API_KEY);
    const medias = await response.json();
    const ids = getIds();
    const csv = createCSV(ids.length ? medias.filter(media => ids.includes(media.hashed_id)) : medias);
    saveCSV(csv);
  } catch (exp) {
    console.log('Error occured: ' + exp.message);
  }
};

getVideos();