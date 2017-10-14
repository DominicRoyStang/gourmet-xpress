
CREATE TABLE IF NOT EXISTS ClientOrder (
	orderId INT AUTO_INCREMENT PRIMARY KEY,
    userId int NOT NULL,
    mealId int NOT NULL,
    productType int NOT NULL,
    orderdate TIMESTAMP NOT NULL,
	
    FOREIGN KEY (userId) REFERENCES Customer(userId),
    FOREIGN KEY (mealId) REFERENCES Meal(mealId),
    FOREIGN KEY (productType) REFERENCES Products(productType)
)