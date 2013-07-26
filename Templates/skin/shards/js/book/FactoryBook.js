/**
 * Description content file
 *
 * @author u
 * @package
 */

function FactoryBook(){}
FactoryBook.prototype.init = function()
{
	try
	{
		myModels.book.bookResource = new Resource();
        myModels.book.bookResource.initialize();

        myModels.book.research = new Research();
        myModels.book.research.initialize();

        myModels.book.quest = new Quest();
        myModels.book.quest.initialize();
	}
	catch(err)
	{
		console.log(err.message);
	}
};