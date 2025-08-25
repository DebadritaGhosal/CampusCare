const express = require('express');
const mongoose = require('mongoose');
const app = express();
const port = 3000;

// MongoDB connection string (replace with your actual connection string)
const dbURI = 'mongodb+srv://koyelray8617:<db_password>@campuscarecluster.xcyyhzw.mongodb.net/?retryWrites=true&w=majority&appName=CampusCareCluster';

mongoose.connect(dbURI, { useNewUrlParser: true, useUnifiedTopology: true })
  .then(() => console.log('MongoDB connected...'))
  .catch(err => console.error('MongoDB connection error:', err));

// Mongoose connection events
mongoose.connection.on('connected', () => console.log('Mongoose connected to DB'));
mongoose.connection.on('error', (err) => console.error('Mongoose connection error:', err));

app.get('/', (req, res) => {
  res.send('Hello, CampusCare!');
});

app.listen(port, () => {
  console.log(`CampusCare backend listening at http://localhost:${port}`);
});
