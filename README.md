# Theta Forex
## About
Theta Forex is forex trading bot, developed with abstraction, inheritance and polymorphism in mind. The structure of the code makes it super easy to implement additional functionality. The bot works with Oanda, but can easily be changed to work with other brokers if so pleased. The main focus of this application is the structure of the classes.

TA class calculates technical indicators commonly used in the market. State, which inherits from TA, contain all the necessary data for a given pair (e.g EUR/USD) from a point in time, up until the time the class was instantiated. State is also responsible for executing trades. A strategy which is implemented with IStrategy, inherits from STATE and contain the logic for when to buy and sell respectively. Strategy class then assigns a risk component (IRisk) to State (its parent). States are instantiated through the Controller class, which then executes a trade that generated a buy || sell signal.

This structure makes it really easy to implement your own strategies and risk components (position sizing and risk management) that are through interfaces, compatible with the rest of the system. Enjoy!


## Disclaimer
There are absolutely no guarantees that this bot will earn you money. Speculation in currencies is a risky business. This bot should solely be used for learning purposes...
