const express = require('express');
const mongoose = require('mongoose');
const app = express();
const port = 3000;

// Parse JSON bodies
app.use(express.json());

// MongoDB connection string (replace <db_password> with your actual password)
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

// Simple POST /login handler (replace with real auth logic)
app.post('/login', (req, res) => {
  const { email, password } = req.body || {};
  // TODO: replace this with proper user lookup + password verification
  if (!email || !password) {
    return res.status(400).send('Email and password are required');
  }

  // Example stub: accept any credentials for demo, or check a hardcoded user:
  // if (email === 'test@example.com' && password === 'password123') { ... }
  // For now, pretend login is successful:
  // In production, never do this — use hashed passwords, sessions/JWT, DB lookup, etc.
  console.log(`Login attempt for ${email}`);
  // Example: accept login and respond OK
  return res.status(200).send('Login successful');
});

// Serve static files (after API routes or anywhere — order not critical here)
app.use(express.static(__dirname));

app.listen(port, () => {
  console.log(`CampusCare backend listening at http://localhost:${port}`);
});