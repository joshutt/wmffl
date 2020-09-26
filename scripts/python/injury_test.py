import unittest

from python.injury import Injury


class InjuryTestCase(unittest.TestCase):
    def test_basic_injury(self):
        injury = Injury('1234', 'Out')
        self.assertIsNotNone(injury)
        self.assertEqual('1234', injury.id)
        self.assertEqual('Out', injury.status)
        self.assertIsNone(injury.details)
        self.assertIsNone(injury.expectedReturn)

        injury.set_details('Broken foot')
        self.assertEqual('1234', injury.id)
        self.assertEqual('Out', injury.status)
        self.assertEqual('Broken foot', injury.details)
        self.assertIsNone(injury.expectedReturn)

        injury.set_return('Dec 31')
        self.assertEqual('Dec 31', injury.expectedReturn)

    def test_injury_by_json(self):
        json = {'exp_return': 'Nov 8, 2020', 'status': 'Suspended', 'id': '9988', 'details': 'Suspension'}
        injury = Injury.load_by_dict(json)

        self.assertIsNotNone(injury)
        self.assertEqual('9988', injury.id)
        self.assertEqual('Suspended', injury.status)
        self.assertEqual('Suspension', injury.details)
        self.assertEqual('Nov 8, 2020', injury.expectedReturn)


if __name__ == '__main__':
    unittest.main()
